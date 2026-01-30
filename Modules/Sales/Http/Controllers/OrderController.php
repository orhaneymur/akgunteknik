<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Models\Order;
use Modules\Sales\Models\OrderItem;
use Modules\Sales\Http\Requests\StoreOrderRequest;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Services\PriceCalculationService;
use Modules\Inventory\Services\StockReservationService;
use Modules\Sales\Models\OrderStatusHistory;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class OrderController extends BaseController
{
    public function index(Request $request)
    {
        $query = Order::with(['items.product', 'customer', 'statusHistory'])
            ->where('tenant_id', $request->user()->tenant_id);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->get();

        return $this->respondSuccess($orders, 'Orders retrieved successfully.');
    }

    public function store(StoreOrderRequest $request)
    {
        // Validation is handled by FormRequest (including tenant ownership checks)

        try {
            DB::beginTransaction();

            $tenantId = $request->user()->tenant_id;
            // Get tenant's default warehouse
            $warehouse = \Modules\Core\Models\Warehouse::where('tenant_id', $tenantId)->first();
            if (!$warehouse) {
                throw new \Exception('No warehouse found for this tenant.');
            }
            $warehouseId = $warehouse->id;

            $items = $request->items;
            $totalAmount = 0;
            $orderItemsData = [];

            // Get customer if provided
            $customer = null;
            if ($request->customer_id) {
                $customer = Customer::where('id', $request->customer_id)
                    ->where('tenant_id', $tenantId)
                    ->with('customerGroup')
                    ->first();
            }

            // Initialize services
            $priceService = new PriceCalculationService();
            $reservationService = new StockReservationService();

            // Calculate total and prepare items
            foreach ($items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                // Check available stock (current - reserved)
                $availableStock = $reservationService->getAvailableStock($product->id, $warehouseId);
                $requestedQuantity = $item['quantity'];

                if ($availableStock < $requestedQuantity) {
                    DB::rollBack();
                    return $this->respondError(
                        [
                            'items' => [
                                "Ürün '{$product->name}' için yetersiz stok. Mevcut stok: {$availableStock}, İstenen: {$requestedQuantity}"
                            ]
                        ],
                        'Yetersiz stok',
                        422
                    );
                }

                // Calculate price: use provided unit_price, or calculate from price list/customer group
                if (isset($item['unit_price'])) {
                    $unitPrice = $item['unit_price'];
                } else {
                    // Use price calculation service
                    $unitPrice = $priceService->getPrice($product, $customer, $item['quantity']);
                }

                $lineTotal = $unitPrice * $item['quantity'];
                $totalAmount += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            // Generate order number
            $datePrefix = date('Ymd');
            $lastOrder = Order::where('tenant_id', $tenantId)
                ->whereDate('created_at', today())
                ->orderBy('id', 'desc')
                ->first();

            $sequence = $lastOrder ? (int) substr($lastOrder->order_number ?? '0', -4) + 1 : 1;
            $orderNumber = 'ORD-' . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Create Order
            $order = Order::create([
                'tenant_id' => $tenantId,
                'customer_id' => $request->customer_id,
                'order_number' => $orderNumber,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'remaining_amount' => $totalAmount,
                'status' => 'pending',
                'shipping_address' => $request->shipping_address ?? ($customer ? $customer->address : null),
            ]);

            // Create Items
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);
            }

            // Reserve stock (don't deduct yet)
            $reservationResult = $reservationService->reserveStock($order, $items, $warehouseId);

            if (!empty($reservationResult['errors'])) {
                DB::rollBack();
                return $this->respondError(
                    ['items' => $reservationResult['errors']],
                    'Stok rezervasyonu başarısız',
                    422
                );
            }

            // Record status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => 'pending',
                'notes' => 'Sipariş oluşturuldu',
                'changed_by' => $request->user()->id,
            ]);

            DB::commit();

            Log::info('Order created', [
                'order_id' => $order->id,
                'tenant_id' => $tenantId,
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'user_id' => $request->user()->id,
            ]);

            return $this->respondSuccess($order->load('items'), 'Order created successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Order creation failed.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $order = Order::with(['items.product', 'customer', 'statusHistory.changedBy', 'reservedStock'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$order) {
            return $this->respondError([], 'Order not found.', 404);
        }

        return $this->respondSuccess($order, 'Order details retrieved successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'carrier' => 'nullable|string|max:255',
            'tracking_number' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        $order = Order::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$order) {
            return $this->respondError([], 'Order not found.', 404);
        }

        try {
            DB::beginTransaction();

            $oldStatus = $order->status;
            $newStatus = $request->status;

            // Update order status and timestamps
            $updateData = ['status' => $newStatus];

            switch ($newStatus) {
                case 'processing':
                    $updateData['processing_at'] = now();
                    break;
                case 'shipped':
                    $updateData['shipped_at'] = now();
                    $updateData['carrier'] = $request->carrier;
                    $updateData['tracking_number'] = $request->tracking_number;
                    // Fulfill reserved stock (convert to actual stock movement)
                    $reservationService = new StockReservationService();
                    $reservationService->fulfillStock($order);
                    // Deduct stock
                    foreach ($order->items as $item) {
                        InventoryMovement::create([
                            'tenant_id' => $order->tenant_id,
                            'product_id' => $item->product_id,
                            'warehouse_id' => \Modules\Core\Models\Warehouse::where('tenant_id', $order->tenant_id)->first()->id,
                            'quantity' => -1 * $item->quantity,
                            'type' => 'sale',
                            'reference_id' => 'ORDER-' . $order->id,
                        ]);
                    }
                    break;
                case 'delivered':
                    $updateData['delivered_at'] = now();
                    break;
                case 'cancelled':
                    $updateData['cancelled_at'] = now();
                    // Release reserved stock
                    $reservationService = new StockReservationService();
                    $reservationService->releaseStock($order);
                    break;
            }

            $order->update($updateData);

            // Record status history
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'status' => $newStatus,
                'notes' => $request->notes ?? "Status changed from {$oldStatus} to {$newStatus}",
                'changed_by' => $request->user()->id,
            ]);

            DB::commit();

            Log::info('Order status updated', [
                'order_id' => $order->id,
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'user_id' => $request->user()->id,
            ]);

            return $this->respondSuccess($order->load('statusHistory'), 'Order status updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order status update failed', [
                'order_id' => $id,
                'error' => $e->getMessage(),
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to update order status.', 500);
        }
    }
}

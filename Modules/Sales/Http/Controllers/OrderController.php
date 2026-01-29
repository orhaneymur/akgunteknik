<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Models\Order;
use Modules\Sales\Models\OrderItem;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\Validator;

class OrderController extends BaseController
{
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'customer'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->get();

        return $this->respondSuccess($orders, 'Orders retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_id' => 'nullable|exists:customers,id',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

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

            // Calculate total and prepare items
            foreach ($items as $item) {
                $product = Product::where('id', $item['product_id'])
                    ->where('tenant_id', $tenantId)
                    ->lockForUpdate() // Prevent race conditions
                    ->firstOrFail();

                // Use provided unit_price or fallback to base_price
                $unitPrice = isset($item['unit_price']) ? $item['unit_price'] : $product->base_price;
                $lineTotal = $unitPrice * $item['quantity'];
                $totalAmount += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ];
            }

            // Create Order
            $order = Order::create([
                'tenant_id' => $tenantId,
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Create Items and Deduct Stock
            foreach ($orderItemsData as $itemData) {
                $order->items()->create($itemData);

                // Deduct Stock
                InventoryMovement::create([
                    'tenant_id' => $tenantId,
                    'product_id' => $itemData['product_id'],
                    'warehouse_id' => $warehouseId,
                    'quantity' => -1 * $itemData['quantity'], // Negative for deduction
                    'type' => 'sale',
                    'reference_id' => 'ORDER-' . $order->id,
                ]);
            }

            DB::commit();

            return $this->respondSuccess($order->load('items'), 'Order created successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            // Log the error for debugging
            \Illuminate\Support\Facades\Log::error('Order Creation Failed: ' . $e->getMessage());
            return $this->respondError(['error' => $e->getMessage()], 'Order creation failed.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $order = Order::with('items.product')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$order) {
            return $this->respondError([], 'Order not found.', 404);
        }

        return $this->respondSuccess($order, 'Order details retrieved successfully.');
    }
}

<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Models\Quote;
use Modules\Sales\Models\Order;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class QuoteController extends BaseController
{
    public function index(Request $request)
    {
        $quotes = Quote::with('customer')
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(15);

        return $this->respondSuccess($quotes, 'Quotes retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'nullable|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'valid_until' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $quoteItems = [];

            foreach ($request->items as $item) {
                // Fetch product price (assuming passed or fetched, better to fetch to be safe, but for now trusting frontend or fetching inside loop)
                // To be safe and correct, we should fetch the product.
                $product = \Modules\Inventory\Models\Product::find($item['product_id']);
                $unitPrice = $product->base_price;
                $totalPrice = $unitPrice * $item['quantity'];

                $totalAmount += $totalPrice;

                $quoteItems[] = [
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $totalPrice,
                ];
            }

            $quote = Quote::create([
                'tenant_id' => $request->user()->tenant_id,
                'customer_id' => $request->customer_id,
                'quote_number' => 'QT-' . date('Ymd') . '-' . Str::upper(Str::random(4)),
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'valid_until' => $request->valid_until ?? now()->addDays(30),
            ]);

            $quote->items()->createMany($quoteItems);

            DB::commit();

            Log::info('Quote created', [
                'quote_id' => $quote->id,
                'tenant_id' => $request->user()->tenant_id,
                'customer_id' => $request->customer_id,
                'total_amount' => $totalAmount,
                'user_id' => $request->user()->id,
            ]);

            return $this->respondSuccess($quote, 'Quote created successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quote creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create quote.', 500);
        }
    }

    public function show($id)
    {
        $quote = Quote::with(['items.product', 'customer'])
            ->where('id', $id)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->first();

        if (!$quote) {
            return $this->respondError([], 'Quote not found.', 404);
        }

        return $this->respondSuccess($quote, 'Quote retrieved successfully.');
    }

    public function convertToOrder(Request $request, $id)
    {
        $quote = Quote::with('items')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$quote) {
            return $this->respondError([], 'Quote not found.', 404);
        }

        if ($quote->status === 'converted') {
            return $this->respondError([], 'Quote is already converted.', 400);
        }

        try {
            DB::beginTransaction();

            // Get tenant's default warehouse (consistent with OrderController)
            $warehouse = \Modules\Core\Models\Warehouse::where('tenant_id', $quote->tenant_id)->first();
            if (!$warehouse) {
                throw new \Exception('No warehouse found for this tenant.');
            }
            $warehouseId = $warehouse->id;

            // Create Order
            $order = Order::create([
                'tenant_id' => $quote->tenant_id,
                'customer_id' => $quote->customer_id,
                'total_amount' => $quote->total_amount,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Create Order Items and Deduct Stock
            foreach ($quote->items as $qItem) {
                // Verify product belongs to tenant and lock for update
                $product = \Modules\Inventory\Models\Product::where('id', $qItem->product_id)
                    ->where('tenant_id', $quote->tenant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check stock availability
                $currentStock = $product->inventoryMovements()->sum('quantity') ?? 0;
                if ($currentStock < $qItem->quantity) {
                    DB::rollBack();
                    return $this->respondError(
                        [
                            'stock' => [
                                "Ürün '{$product->name}' için yetersiz stok. Mevcut stok: {$currentStock}, İstenen: {$qItem->quantity}"
                            ]
                        ],
                        'Yetersiz stok',
                        422
                    );
                }

                // Deduct Stock
                \Modules\Inventory\Models\InventoryMovement::create([
                    'tenant_id' => $quote->tenant_id,
                    'product_id' => $qItem->product_id,
                    'warehouse_id' => $warehouseId,
                    'quantity' => -$qItem->quantity,
                    'type' => 'sale',
                    'reference_id' => 'ORDER-' . $order->id,
                ]);

                // Order Item
                $order->items()->create([
                    'product_id' => $qItem->product_id,
                    'quantity' => $qItem->quantity,
                    'unit_price' => $qItem->unit_price,
                    'total_price' => $qItem->total_price,
                ]);
            }

            // Update Quote Status
            $quote->status = 'converted';
            $quote->save();

            DB::commit();

            Log::info('Quote converted to order', [
                'quote_id' => $quote->id,
                'order_id' => $order->id,
                'tenant_id' => $quote->tenant_id,
                'total_amount' => $quote->total_amount,
                'user_id' => $request->user()->id,
            ]);

            return $this->respondSuccess($order->load('items'), 'Quote converted to Order successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Quote conversion failed', [
                'quote_id' => $id,
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to convert quote.', 500);
        }
    }
}

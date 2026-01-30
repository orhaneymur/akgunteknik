<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Models\ExchangeRate;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\ImportCost;
use Modules\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends BaseController
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', 'warehouse', 'importCosts'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->get();

        return $this->respondSuccess($orders, 'Purchase orders retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'currency' => 'nullable|string|size:3|in:TRY,USD,EUR,GBP',
            'exchange_rate' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            $currency = $request->currency ?? 'TRY';
            $exchangeRate = $request->exchange_rate;

            // If currency is not TRY and exchange_rate not provided, try to get latest rate
            if ($currency !== 'TRY' && !$exchangeRate) {
                $latestRate = ExchangeRate::getLatestRate($currency);
                if ($latestRate) {
                    $exchangeRate = $latestRate;
                } else {
                    return $this->respondError(['exchange_rate' => 'Exchange rate is required for foreign currency.'], 'Exchange rate not found.', 422);
                }
            }

            // If TRY, exchange_rate is 1
            if ($currency === 'TRY') {
                $exchangeRate = 1.0;
            }

            $totalAmount = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $lineTotal = $item['quantity'] * $item['unit_cost'];
                $totalAmount += $lineTotal;

                $itemsData[] = [
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_cost' => $item['unit_cost'],
                    'total_cost' => $lineTotal,
                ];
            }

            // Calculate TL equivalent
            $totalAmountTl = $totalAmount * $exchangeRate;

            $po = PurchaseOrder::create([
                'tenant_id' => $request->user()->tenant_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'status' => 'pending',
                'total_amount' => $totalAmount,
                'currency' => $currency,
                'exchange_rate' => $exchangeRate,
                'total_amount_tl' => $totalAmountTl,
            ]);

            $po->items()->createMany($itemsData);

            DB::commit();

            Log::info('Purchase order created', [
                'purchase_order_id' => $po->id,
                'tenant_id' => $request->user()->tenant_id,
                'supplier_id' => $request->supplier_id,
                'total_amount' => $totalAmount,
                'user_id' => $request->user()->id,
            ]);

            return $this->respondSuccess($po->load(['items', 'importCosts']), 'Purchase Order created successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase order creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create Purchase Order.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $po = PurchaseOrder::with(['items.product', 'supplier', 'warehouse', 'importCosts'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$po) {
            return $this->respondError([], 'Purchase order not found.', 404);
        }

        return $this->respondSuccess($po, 'Purchase order retrieved successfully.');
    }

    public function receive(Request $request, $id)
    {
        $po = PurchaseOrder::with('items')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$po) {
            return $this->respondError([], 'Order not found.', 404);
        }

        if ($po->status !== 'pending') {
            return $this->respondError([], 'Order is already processed.', 400);
        }

        try {
            DB::beginTransaction();

            $po->update([
                'status' => 'received',
                'received_at' => now(),
            ]);

            foreach ($po->items as $item) {
                // Verify product belongs to tenant and lock for update
                $product = \Modules\Inventory\Models\Product::where('id', $item->product_id)
                    ->where('tenant_id', $po->tenant_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                InventoryMovement::create([
                    'tenant_id' => $po->tenant_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $po->warehouse_id,
                    'quantity' => $item->quantity,
                    'type' => 'purchase',
                    'reference_id' => 'PO-' . $po->id,
                ]);
            }

            DB::commit();

            Log::info('Purchase order received', [
                'purchase_order_id' => $po->id,
                'tenant_id' => $po->tenant_id,
                'warehouse_id' => $po->warehouse_id,
                'user_id' => $request->user()->id,
            ]);

            return $this->respondSuccess($po, 'Purchase Order received and stock updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Purchase order receive failed', [
                'purchase_order_id' => $id,
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to receive order.', 500);
        }
    }
}

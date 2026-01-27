<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PurchaseOrderController extends BaseController
{
    public function index(Request $request)
    {
        $orders = PurchaseOrder::with(['supplier', 'warehouse'])
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

            $po = PurchaseOrder::create([
                'tenant_id' => $request->user()->tenant_id,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'status' => 'pending',
                'total_amount' => $totalAmount,
            ]);

            $po->items()->createMany($itemsData);

            DB::commit();

            return $this->respondSuccess($po->load('items'), 'Purchase Order created successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create Purchase Order.', 500);
        }
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
                InventoryMovement::create([
                    'tenant_id' => $po->tenant_id,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $po->warehouse_id,
                    'quantity' => $item->quantity, // Positive for addition
                    'type' => 'purchase',
                    'reference_id' => 'PO-' . $po->id,
                ]);
            }

            DB::commit();

            return $this->respondSuccess($po, 'Purchase Order received and stock updated.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to receive order.', 500);
        }
    }
}

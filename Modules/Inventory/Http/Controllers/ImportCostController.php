<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\ImportCost;
use Modules\Inventory\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ImportCostController extends BaseController
{
    public function index(Request $request, $purchaseOrderId)
    {
        $po = PurchaseOrder::where('id', $purchaseOrderId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$po) {
            return $this->respondError([], 'Purchase order not found.', 404);
        }

        $costs = ImportCost::where('purchase_order_id', $purchaseOrderId)
            ->orderBy('cost_date', 'desc')
            ->get();

        return $this->respondSuccess($costs, 'Import costs retrieved successfully.');
    }

    public function store(Request $request, $purchaseOrderId)
    {
        $po = PurchaseOrder::where('id', $purchaseOrderId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$po) {
            return $this->respondError([], 'Purchase order not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'cost_type' => 'required|in:customs,tax,freight,insurance,other',
            'description' => 'nullable|string|max:255',
            'amount' => 'required|numeric|min:0',
            'currency' => 'nullable|string|size:3|in:TRY,USD,EUR,GBP',
            'cost_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            $currency = $request->currency ?? 'TRY';
            $amount = $request->amount;

            // If currency is not TRY, convert to TL using purchase order's exchange rate
            if ($currency !== 'TRY' && $po->exchange_rate) {
                $amount = $amount * $po->exchange_rate;
            }

            $cost = ImportCost::create([
                'purchase_order_id' => $purchaseOrderId,
                'cost_type' => $request->cost_type,
                'description' => $request->description,
                'amount' => $amount, // Store in TL
                'currency' => $currency, // Original currency
                'cost_date' => $request->cost_date ?? now(),
                'notes' => $request->notes,
            ]);

            // Recalculate total import cost
            $this->recalculateTotalImportCost($po);

            DB::commit();

            return $this->respondSuccess($cost, 'Import cost added successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to add import cost.', 500);
        }
    }

    public function update(Request $request, $purchaseOrderId, $id)
    {
        $po = PurchaseOrder::where('id', $purchaseOrderId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$po) {
            return $this->respondError([], 'Purchase order not found.', 404);
        }

        $cost = ImportCost::where('id', $id)
            ->where('purchase_order_id', $purchaseOrderId)
            ->first();

        if (!$cost) {
            return $this->respondError([], 'Import cost not found.', 404);
        }

        $validator = Validator::make($request->all(), [
            'cost_type' => 'sometimes|in:customs,tax,freight,insurance,other',
            'description' => 'nullable|string|max:255',
            'amount' => 'sometimes|numeric|min:0',
            'currency' => 'nullable|string|size:3|in:TRY,USD,EUR,GBP',
            'cost_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            $currency = $request->currency ?? $cost->currency;
            $amount = $request->has('amount') ? $request->amount : $cost->amount;

            // If currency is not TRY, convert to TL using purchase order's exchange rate
            if ($currency !== 'TRY' && $po->exchange_rate) {
                $amount = $amount * $po->exchange_rate;
            }

            $cost->update([
                'cost_type' => $request->cost_type ?? $cost->cost_type,
                'description' => $request->description ?? $cost->description,
                'amount' => $amount,
                'currency' => $currency,
                'cost_date' => $request->cost_date ?? $cost->cost_date,
                'notes' => $request->notes ?? $cost->notes,
            ]);

            // Recalculate total import cost
            $this->recalculateTotalImportCost($po);

            DB::commit();

            return $this->respondSuccess($cost, 'Import cost updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to update import cost.', 500);
        }
    }

    public function destroy(Request $request, $purchaseOrderId, $id)
    {
        $po = PurchaseOrder::where('id', $purchaseOrderId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$po) {
            return $this->respondError([], 'Purchase order not found.', 404);
        }

        $cost = ImportCost::where('id', $id)
            ->where('purchase_order_id', $purchaseOrderId)
            ->first();

        if (!$cost) {
            return $this->respondError([], 'Import cost not found.', 404);
        }

        try {
            DB::beginTransaction();

            $cost->delete();

            // Recalculate total import cost
            $this->recalculateTotalImportCost($po);

            DB::commit();

            return $this->respondSuccess(null, 'Import cost deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->respondError(['error' => $e->getMessage()], 'Failed to delete import cost.', 500);
        }
    }

    /**
     * Recalculate total import cost and update purchase order
     */
    protected function recalculateTotalImportCost(PurchaseOrder $po)
    {
        $totalImportCost = ImportCost::where('purchase_order_id', $po->id)
            ->sum('amount');

        // Total TL amount = product cost (already in total_amount_tl) + import costs
        // But we need to recalculate from base: product cost in TL + import costs
        $productCostTl = $po->total_amount * $po->exchange_rate;
        $newTotalTl = $productCostTl + $totalImportCost;

        $po->update([
            'total_amount_tl' => $newTotalTl,
        ]);
    }
}

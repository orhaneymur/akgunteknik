<?php

namespace Modules\Sales\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Models\ReturnRequest;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Customer\Models\Customer;
use Modules\Inventory\Models\Supplier;
use Modules\Finance\Models\Transaction;

class ReturnController extends BaseController
{
    public function index(Request $request)
    {
        $returns = ReturnRequest::with(['customer', 'supplier', 'items.product'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(50);

        return $this->respondSuccess($returns, 'Return requests retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:sale_return,purchase_return',
            'customer_id' => 'required_if:type,sale_return|nullable|exists:customers,id',
            'supplier_id' => 'required_if:type,purchase_return|nullable|exists:suppliers,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            return DB::transaction(function () use ($request, $validator) {
                $validated = $validator->validated();
                $tenantId = $request->user()->tenant_id;

                // Verify customer/supplier belong to tenant
                if ($validated['type'] === 'sale_return' && $validated['customer_id']) {
                    Customer::where('id', $validated['customer_id'])
                        ->where('tenant_id', $tenantId)
                        ->firstOrFail();
                }
                if ($validated['type'] === 'purchase_return' && $validated['supplier_id']) {
                    Supplier::where('id', $validated['supplier_id'])
                        ->where('tenant_id', $tenantId)
                        ->firstOrFail();
                }

                $totalAmount = 0;
                foreach ($validated['items'] as $item) {
                    $totalAmount += $item['quantity'] * $item['price'];
                }

                $return = ReturnRequest::create([
                    'tenant_id' => $tenantId,
                    'type' => $validated['type'],
                    'status' => 'pending',
                    'customer_id' => $validated['customer_id'] ?? null,
                    'supplier_id' => $validated['supplier_id'] ?? null,
                    'date' => $validated['date'],
                    'notes' => $validated['notes'] ?? null,
                    'total_amount' => $totalAmount
                ]);

                foreach ($validated['items'] as $item) {
                    $return->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'total' => $item['quantity'] * $item['price']
                    ]);
                }

                Log::info('Return request created', [
                    'return_id' => $return->id,
                    'tenant_id' => $tenantId,
                    'type' => $validated['type'],
                    'user_id' => $request->user()->id,
                ]);

                return $this->respondSuccess($return->load('items.product'), 'Return request created successfully.', 201);
            });
        } catch (\Exception $e) {
            Log::error('Return request creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create return request.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $return = ReturnRequest::with(['customer', 'supplier', 'items.product'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$return) {
            return $this->respondError([], 'Return request not found.', 404);
        }

        return $this->respondSuccess($return, 'Return request retrieved successfully.');
    }

    public function approve(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $return = ReturnRequest::with('items')
                    ->where('id', $id)
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->firstOrFail();

                if ($return->status !== 'pending') {
                    return $this->respondError([], 'Return already processed.', 400);
                }

                // Get default warehouse for tenant
                $warehouse = \Modules\Core\Models\Warehouse::where('tenant_id', $return->tenant_id)->first();
                if (!$warehouse) {
                    throw new \Exception('No warehouse found for this tenant.');
                }

                // 1. Inventory Adjustment
                foreach ($return->items as $item) {
                    // Verify product belongs to tenant
                    $product = \Modules\Inventory\Models\Product::where('id', $item->product_id)
                        ->where('tenant_id', $return->tenant_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    if ($return->type === 'sale_return') {
                        // Customer returned item -> Stock INCREASES
                        InventoryMovement::create([
                            'tenant_id' => $return->tenant_id,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $warehouse->id,
                            'quantity' => $item->quantity,
                            'type' => 'sale_return',
                            'reference_id' => 'RETURN-' . $return->id,
                        ]);
                    } elseif ($return->type === 'purchase_return') {
                        // Return to supplier -> Stock DECREASES
                        InventoryMovement::create([
                            'tenant_id' => $return->tenant_id,
                            'product_id' => $item->product_id,
                            'warehouse_id' => $warehouse->id,
                            'quantity' => -$item->quantity,
                            'type' => 'purchase_return',
                            'reference_id' => 'RETURN-' . $return->id,
                        ]);
                    }
                }

                // 2. Financial Adjustment (Balance)
                if ($return->type === 'sale_return' && $return->customer_id) {
                    $customer = Customer::where('id', $return->customer_id)
                        ->where('tenant_id', $return->tenant_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Reduce customer debt (balance decreases)
                    $customer->decrement('balance', $return->total_amount);

                    Transaction::create([
                        'tenant_id' => $return->tenant_id,
                        'customer_id' => $customer->id,
                        'type' => 'return',
                        'amount' => $return->total_amount,
                        'date' => $return->date,
                        'description' => 'Satış İadesi #' . $return->id
                    ]);

                } elseif ($return->type === 'purchase_return' && $return->supplier_id) {
                    $supplier = Supplier::where('id', $return->supplier_id)
                        ->where('tenant_id', $return->tenant_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Reduce our debt to supplier (balance decreases)
                    $supplier->decrement('balance', $return->total_amount);

                    Transaction::create([
                        'tenant_id' => $return->tenant_id,
                        'supplier_id' => $supplier->id,
                        'type' => 'return',
                        'amount' => $return->total_amount,
                        'date' => $return->date,
                        'description' => 'Alış İadesi #' . $return->id
                    ]);
                }

                $return->update(['status' => 'approved']);

                Log::info('Return request approved', [
                    'return_id' => $return->id,
                    'tenant_id' => $return->tenant_id,
                    'type' => $return->type,
                    'user_id' => $request->user()->id,
                ]);

                return $this->respondSuccess($return->load('items.product'), 'Return request approved successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Return request approval failed', [
                'return_id' => $id,
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to approve return request.', 500);
        }
    }
}

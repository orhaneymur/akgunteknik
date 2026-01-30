<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Inventory\Models\InventoryMovement;

class InventoryTransferController extends BaseController
{
    public function index(Request $request)
    {
        $transfers = InventoryTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(50);

        return $this->respondSuccess($transfers, 'Inventory transfers retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            return DB::transaction(function () use ($request, $validator) {
                $validated = $validator->validated();
                $tenantId = $request->user()->tenant_id;

                // Verify warehouses belong to tenant
                $fromWarehouse = \Modules\Core\Models\Warehouse::where('id', $validated['from_warehouse_id'])
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();
                $toWarehouse = \Modules\Core\Models\Warehouse::where('id', $validated['to_warehouse_id'])
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                $transfer = InventoryTransfer::create([
                    'tenant_id' => $tenantId,
                    'from_warehouse_id' => $validated['from_warehouse_id'],
                    'to_warehouse_id' => $validated['to_warehouse_id'],
                    'date' => $validated['date'],
                    'notes' => $validated['notes'] ?? null,
                    'status' => 'pending',
                ]);

                foreach ($validated['items'] as $item) {
                    $transfer->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                    ]);
                }

                Log::info('Inventory transfer created', [
                    'transfer_id' => $transfer->id,
                    'tenant_id' => $tenantId,
                    'from_warehouse_id' => $validated['from_warehouse_id'],
                    'to_warehouse_id' => $validated['to_warehouse_id'],
                    'user_id' => $request->user()->id,
                ]);

                return $this->respondSuccess($transfer->load('items.product'), 'Inventory transfer created successfully.', 201);
            });
        } catch (\Exception $e) {
            Log::error('Inventory transfer creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create inventory transfer.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $transfer = InventoryTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$transfer) {
            return $this->respondError([], 'Inventory transfer not found.', 404);
        }

        return $this->respondSuccess($transfer, 'Inventory transfer retrieved successfully.');
    }

    public function complete(Request $request, $id)
    {
        try {
            return DB::transaction(function () use ($request, $id) {
                $transfer = InventoryTransfer::with('items')
                    ->where('id', $id)
                    ->where('tenant_id', $request->user()->tenant_id)
                    ->firstOrFail();

                if ($transfer->status !== 'pending') {
                    return $this->respondError([], 'Transfer already completed or cancelled.', 400);
                }

                foreach ($transfer->items as $item) {
                    // Verify product belongs to tenant
                    $product = \Modules\Inventory\Models\Product::where('id', $item->product_id)
                        ->where('tenant_id', $transfer->tenant_id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Check stock availability in source warehouse
                    $sourceStock = InventoryMovement::where('tenant_id', $transfer->tenant_id)
                        ->where('product_id', $item->product_id)
                        ->where('warehouse_id', $transfer->from_warehouse_id)
                        ->sum('quantity') ?? 0;

                    if ($sourceStock < $item->quantity) {
                        return $this->respondError(
                            [
                                'stock' => [
                                    "Ürün '{$product->name}' için kaynak depoda yetersiz stok. Mevcut: {$sourceStock}, İstenen: {$item->quantity}"
                                ]
                            ],
                            'Yetersiz stok',
                            422
                        );
                    }

                    // 1. Remove from Source
                    InventoryMovement::create([
                        'tenant_id' => $transfer->tenant_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $transfer->from_warehouse_id,
                        'quantity' => -$item->quantity,
                        'type' => 'transfer',
                        'reference_id' => 'TRANSFER-' . $transfer->id,
                    ]);

                    // 2. Add to Destination
                    InventoryMovement::create([
                        'tenant_id' => $transfer->tenant_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => $transfer->to_warehouse_id,
                        'quantity' => $item->quantity,
                        'type' => 'transfer',
                        'reference_id' => 'TRANSFER-' . $transfer->id,
                    ]);
                }

                $transfer->update(['status' => 'completed']);

                Log::info('Inventory transfer completed', [
                    'transfer_id' => $transfer->id,
                    'tenant_id' => $transfer->tenant_id,
                    'user_id' => $request->user()->id,
                ]);

                return $this->respondSuccess($transfer->load('items.product'), 'Inventory transfer completed successfully.');
            });
        } catch (\Exception $e) {
            Log::error('Inventory transfer completion failed', [
                'transfer_id' => $id,
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to complete inventory transfer.', 500);
        }
    }

    public function cancel(Request $request, $id)
    {
        $transfer = InventoryTransfer::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$transfer) {
            return $this->respondError([], 'Inventory transfer not found.', 404);
        }

        if ($transfer->status !== 'pending') {
            return $this->respondError([], 'Cannot cancel completed transfer.', 400);
        }

        $transfer->update(['status' => 'cancelled']);

        Log::info('Inventory transfer cancelled', [
            'transfer_id' => $transfer->id,
            'tenant_id' => $transfer->tenant_id,
            'user_id' => $request->user()->id,
        ]);

        return $this->respondSuccess($transfer, 'Inventory transfer cancelled successfully.');
    }
}

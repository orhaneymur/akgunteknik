<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\InventoryTransfer;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Inventory\Models\InventoryTransferItem; // Correct import

class InventoryTransferController extends Controller
{
    public function index()
    {
        return InventoryTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])
            ->latest()
            ->paginate(50);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'from_warehouse_id' => 'required|exists:warehouses,id',
            'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        return DB::transaction(function () use ($validated) {
            $transfer = InventoryTransfer::create([
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
                'status' => 'pending', // Draft initially
            ]);

            foreach ($validated['items'] as $item) {
                $transfer->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                ]);
            }

            return $transfer->load('items.product');
        });
    }

    public function show($id)
    {
        return InventoryTransfer::with(['fromWarehouse', 'toWarehouse', 'items.product'])->findOrFail($id);
    }

    public function complete($id)
    {
        return DB::transaction(function () use ($id) {
            $transfer = InventoryTransfer::with('items')->findOrFail($id);

            if ($transfer->status !== 'pending') {
                return response()->json(['message' => 'Transfer already completed or cancelled'], 400);
            }

            foreach ($transfer->items as $item) {
                // 1. Remove from Source
                InventoryMovement::create([
                    'tenant_id' => $transfer->tenant_id ?? 1, // Fix tenant logic later if needed
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'quantity' => -$item->quantity, // Negative
                    'type' => 'transfer',
                    'reference_id' => $transfer->id,
                ]);

                // 2. Add to Destination
                InventoryMovement::create([
                    'tenant_id' => $transfer->tenant_id ?? 1,
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'quantity' => $item->quantity, // Positive
                    'type' => 'transfer',
                    'reference_id' => $transfer->id,
                ]);
            }

            $transfer->update(['status' => 'completed']);

            return $transfer;
        });
    }

    public function cancel($id)
    {
        $transfer = InventoryTransfer::findOrFail($id);
        if ($transfer->status !== 'pending') {
            return response()->json(['message' => 'Cannot cancel completed transfer'], 400);
        }
        $transfer->update(['status' => 'cancelled']);
        return $transfer;
    }
}

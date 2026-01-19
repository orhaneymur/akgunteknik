<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\InventoryLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends BaseController
{
    /**
     * Add stock to a warehouse.
     * 
     * This method:
     * 1. Validates product_id, warehouse_id, and quantity
     * 2. Finds or creates InventoryLevel record
     * 3. Increments the quantity
     * 4. Creates a StockTransaction record (type: 'in')
     * 5. Returns the new stock level
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function addStock(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validate request
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity' => 'required|integer|min:1',
            'reference_no' => 'nullable|string|max:255',
        ]);

        // Verify product belongs to user's tenant
        $product = DB::table('products')
            ->where('id', $validated['product_id'])
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$product) {
            return $this->respondError(
                ['product_id' => ['Product not found or access denied.']],
                'Product not found',
                404
            );
        }

        // Verify warehouse belongs to user's tenant
        $warehouse = DB::table('warehouses')
            ->where('id', $validated['warehouse_id'])
            ->where('tenant_id', $user->tenant_id)
            ->first();

        if (!$warehouse) {
            return $this->respondError(
                ['warehouse_id' => ['Warehouse not found or access denied.']],
                'Warehouse not found',
                404
            );
        }

        // Start transaction
        DB::beginTransaction();

        try {
            // Find or create InventoryLevel record
            $inventoryLevel = InventoryLevel::firstOrCreate(
                [
                    'warehouse_id' => $validated['warehouse_id'],
                    'product_id' => $validated['product_id'],
                ],
                [
                    'tenant_id' => $user->tenant_id,
                    'quantity' => 0,
                    'critical_level' => 5,
                ]
            );

            // Increment quantity
            $inventoryLevel->increment('quantity', $validated['quantity']);

            // Create StockTransaction record (type: 'in')
            DB::table('stock_transactions')->insert([
                'tenant_id' => $user->tenant_id,
                'warehouse_id' => $validated['warehouse_id'],
                'product_id' => $validated['product_id'],
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'reference_no' => $validated['reference_no'] ?? null,
                'user_id' => $user->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();

            // Refresh to get latest data
            $inventoryLevel->refresh();

            // Load relationships for response
            $inventoryLevel->load('product', 'warehouse');

            return $this->respondSuccess([
                'inventory_level' => $inventoryLevel,
                'new_quantity' => $inventoryLevel->quantity,
            ], 'Stock added successfully');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->respondError(
                ['error' => $e->getMessage()],
                'Failed to add stock',
                500
            );
        }
    }
}


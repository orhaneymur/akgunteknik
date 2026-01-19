<?php

namespace Modules\Inventory\Http\Controllers;

use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\InventoryLevel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends BaseController
{
    /**
     * List products with optional warehouse filter.
     * 
     * Query params:
     * - warehouse_id: Filter products and show quantity for specific warehouse
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $warehouseId = $request->query('warehouse_id');

        $query = Product::where('tenant_id', $user->tenant_id)
            ->where('is_active', true);

        // If warehouse_id is provided, join with inventory_levels
        if ($warehouseId) {
            $query->leftJoin('inventory_levels', function ($join) use ($warehouseId) {
                $join->on('products.id', '=', 'inventory_levels.product_id')
                    ->where('inventory_levels.warehouse_id', '=', $warehouseId);
            })
            ->select(
                'products.*',
                DB::raw('COALESCE(inventory_levels.quantity, 0) as quantity'),
                DB::raw('COALESCE(inventory_levels.critical_level, 5) as critical_level')
            );
        } else {
            $query->select('products.*');
        }

        $products = $query->get();

        return $this->respondSuccess($products, 'Products retrieved successfully');
    }

    /**
     * Create a new product and initialize inventory level for user's active warehouse.
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:255',
            'purchase_price' => 'required|numeric|min:0',
            'sale_price' => 'required|numeric|min:0',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'compatibility' => 'nullable|array',
            'compatibility.*' => 'string',
            'is_active' => 'nullable|boolean',
        ]);

        // Start transaction
        DB::beginTransaction();

        try {
            // Create product
            $product = Product::create([
                'tenant_id' => $user->tenant_id,
                'name' => $validated['name'],
                'sku' => $validated['sku'],
                'barcode' => $validated['barcode'] ?? null,
                'purchase_price' => $validated['purchase_price'],
                'sale_price' => $validated['sale_price'],
                'tax_rate' => $validated['tax_rate'] ?? 18.00,
                'compatibility' => $validated['compatibility'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Get user's active warehouse (from branch or first warehouse)
            $warehouseId = null;
            
            if ($user->branch_id) {
                // Get warehouse from branch
                $branch = DB::table('branches')
                    ->where('id', $user->branch_id)
                    ->where('tenant_id', $user->tenant_id)
                    ->first();
                
                if ($branch) {
                    $warehouseId = $branch->warehouse_id;
                }
            }

            // If no warehouse from branch, get first active warehouse for tenant
            if (!$warehouseId) {
                $warehouse = DB::table('warehouses')
                    ->where('tenant_id', $user->tenant_id)
                    ->where('is_active', true)
                    ->first();
                
                if ($warehouse) {
                    $warehouseId = $warehouse->id;
                }
            }

            // Create inventory level entry (0 quantity) for the warehouse
            if ($warehouseId) {
                InventoryLevel::create([
                    'tenant_id' => $user->tenant_id,
                    'warehouse_id' => $warehouseId,
                    'product_id' => $product->id,
                    'quantity' => 0,
                    'critical_level' => 5,
                ]);
            }

            DB::commit();

            // Load relationships for response
            $product->load('stocks.warehouse');

            return $this->respondSuccess($product, 'Product created successfully', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return $this->respondError(
                ['error' => $e->getMessage()],
                'Failed to create product',
                500
            );
        }
    }
}


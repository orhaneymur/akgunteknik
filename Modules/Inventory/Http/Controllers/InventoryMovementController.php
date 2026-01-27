<?php

namespace Modules\Inventory\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Inventory\Models\InventoryMovement;

class InventoryMovementController extends BaseController
{
    public function index(Request $request)
    {
        $query = InventoryMovement::with(['product', 'warehouse'])
            ->where('tenant_id', $request->user()->tenant_id);

        // Filter by Product
        if ($request->has('product_id') && $request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        // Filter by Type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        $movements = $query->latest()->paginate(15);

        return $this->respondSuccess($movements, 'Inventory movements retrieved successfully.');
    }
}

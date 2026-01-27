<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Core\Models\Warehouse;

class WarehouseController extends BaseController
{
    public function index(Request $request)
    {
        $warehouses = Warehouse::where('tenant_id', $request->user()->tenant_id)
            ->get();

        return $this->respondSuccess($warehouses, 'Warehouses retrieved successfully.');
    }
}

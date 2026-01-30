<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Models\TaxRate;

class TaxRateController extends BaseController
{
    public function index(Request $request)
    {
        $taxRates = TaxRate::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return $this->respondSuccess($taxRates, 'Tax rates retrieved successfully.');
    }

    public function show(Request $request, $id)
    {
        $taxRate = TaxRate::find($id);

        if (!$taxRate) {
            return $this->respondError([], 'Tax rate not found.', 404);
        }

        return $this->respondSuccess($taxRate, 'Tax rate retrieved successfully.');
    }
}

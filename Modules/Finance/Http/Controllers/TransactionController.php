<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Finance\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::with('safe');

        if ($request->has('payable_type') && $request->has('payable_id')) {
            $query->where('payable_type', $request->payable_type)
                ->where('payable_id', $request->payable_id);
        }

        if ($request->has('safe_id')) {
            $query->where('safe_id', $request->safe_id);
        }

        return $query->latest()->paginate(50);
    }
}

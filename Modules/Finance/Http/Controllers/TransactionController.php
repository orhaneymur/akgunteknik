<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Finance\Models\Transaction;

class TransactionController extends BaseController
{
    public function index(Request $request)
    {
        $query = Transaction::with('safe')
            ->where('tenant_id', $request->user()->tenant_id);

        if ($request->has('payable_type') && $request->has('payable_id')) {
            $query->where('payable_type', $request->payable_type)
                ->where('payable_id', $request->payable_id);
        }

        if ($request->has('safe_id')) {
            $query->where('safe_id', $request->safe_id);
        }

        $transactions = $query->latest()->paginate(50);

        return $this->respondSuccess($transactions, 'Transactions retrieved successfully.');
    }

    public function store(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric',
            'transaction_date' => 'required|date',
            'type' => 'required|in:collection,payment',
            'payable_type' => 'required|in:customer,supplier',
            'payable_id' => 'required|integer',
            'method' => 'required|string',
        ]);

        $payableType = null;
        if ($request->payable_type === 'customer') {
            $payableType = 'Modules\\Customer\\Models\\Customer';
        } else if ($request->payable_type === 'supplier') {
            $payableType = 'Modules\\Inventory\\Models\\Supplier';
        }

        $transaction = Transaction::create([
            'tenant_id' => $request->user()->tenant_id,
            'amount' => $request->amount,
            'type' => $request->type === 'collection' ? 'deposit' : 'withdrawal',
            'payable_type' => $payableType,
            'payable_id' => $request->payable_id,
            'date' => $request->transaction_date,
            'description' => $request->description . ($request->method ? " (Yöntem: {$request->method})" : ""),
        ]);

        // If 'deposit' (collection from customer), Customer balance decreases (borç düşer / alacak artar?)
        // Actually Customer Balance = (Sales) - (Collections).
        // This logic is usually computed or updated.
        // For simplicity, we just store transaction. 
        // Logic for balance calculation usually sums these up.

        return $this->respondSuccess($transaction, 'Transaction created successfully.', 201);
    }
}

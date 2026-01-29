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
            // We need a safe_id if it affects a safe?
            // PaymentModal currently doesn't select a safe! 
            // It just records the transaction on the account?
            // "Ödeme Yöntemi" is "Nakit", "Havale" etc.
            // But it doesn't ask "Hangi Kasa?".
            // For now, let's leave safe_id nullable or generic.
        ]);

        // If 'deposit' (collection from customer), Customer balance decreases (borç düşer / alacak artar?)
        // Actually Customer Balance = (Sales) - (Collections).
        // This logic is usually computed or updated.
        // For simplicity, we just store transaction. 
        // Logic for balance calculation usually sums these up.

        return response()->json(['success' => true, 'data' => $transaction], 201);
    }
}

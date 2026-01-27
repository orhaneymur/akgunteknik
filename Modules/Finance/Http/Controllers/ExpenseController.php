<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Transaction;
use Modules\Finance\Models\Safe;

class ExpenseController extends Controller
{
    public function index()
    {
        return Expense::with('category', 'safe')->latest()->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,id',
            'safe_id' => 'nullable|exists:safes,id',
            'document_path' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Create Expense
            $expense = Expense::create($validated);

            // 2. If paid from safe, deduct balance and create transaction
            if (!empty($validated['safe_id'])) {
                $safe = Safe::findOrFail($validated['safe_id']);

                // Deduct balance
                $safe->decrement('balance', $validated['amount']);

                // Create Transaction
                Transaction::create([
                    'tenant_id' => $expense->tenant_id,
                    'payable_type' => Expense::class,
                    'payable_id' => $expense->id,
                    'safe_id' => $safe->id,
                    'type' => 'withdrawal',
                    'amount' => $validated['amount'],
                    'currency' => $safe->currency,
                    'description' => $validated['description'],
                    'date' => $validated['date'],
                    'reference_type' => Expense::class,
                    'reference_id' => $expense->id,
                ]);
            }

            return $expense;
        });
    }

    public function show($id)
    {
        return Expense::with('category', 'safe')->findOrFail($id);
    }
}

<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Finance\Models\Expense;
use Modules\Finance\Models\Transaction;
use Modules\Finance\Models\Safe;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends BaseController
{
    public function index(Request $request)
    {
        $query = Expense::with('category', 'safe')
            ->where('tenant_id', $request->user()->tenant_id);

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%");
            });
        }

        // Filter by category
        if ($request->has('category_id') && $request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->start_date) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && $request->end_date) {
            $query->where('date', '<=', $request->end_date);
        }

        // If 'all' parameter is provided, return all without pagination
        if ($request->has('all')) {
            $expenses = $query->latest()->get();
            return $this->respondSuccess($expenses, 'All expenses retrieved successfully.');
        }

        // Pagination with default 15 items per page
        $perPage = $request->input('per_page', 15);
        $expenses = $query->latest()->paginate($perPage);

        return $this->respondSuccess($expenses, 'Expenses retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category_id' => 'required|exists:expense_categories,id',
            'safe_id' => 'nullable|exists:safes,id',
            'document_path' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            return DB::transaction(function () use ($request, $validator) {
                $validated = $validator->validated();
                $tenantId = $request->user()->tenant_id;

                // 1. Create Expense
                $expense = Expense::create([
                    'tenant_id' => $tenantId,
                    'description' => $validated['description'],
                    'amount' => $validated['amount'],
                    'date' => $validated['date'],
                    'category_id' => $validated['category_id'],
                    'safe_id' => $validated['safe_id'] ?? null,
                    'document_path' => $validated['document_path'] ?? null,
                ]);

                // 2. If paid from safe, deduct balance and create transaction
                if (!empty($validated['safe_id'])) {
                    $safe = Safe::where('id', $validated['safe_id'])
                        ->where('tenant_id', $tenantId)
                        ->lockForUpdate()
                        ->firstOrFail();

                    // Deduct balance
                    $safe->decrement('balance', $validated['amount']);

                    // Create Transaction
                    Transaction::create([
                        'tenant_id' => $tenantId,
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

                Log::info('Expense created', [
                    'expense_id' => $expense->id,
                    'tenant_id' => $tenantId,
                    'amount' => $validated['amount'],
                    'user_id' => $request->user()->id,
                ]);

                return $this->respondSuccess($expense->load('category', 'safe'), 'Expense created successfully.', 201);
            });
        } catch (\Exception $e) {
            Log::error('Expense creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create expense.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $expense = Expense::with('category', 'safe')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$expense) {
            return $this->respondError([], 'Expense not found.', 404);
        }

        return $this->respondSuccess($expense, 'Expense retrieved successfully.');
    }
}

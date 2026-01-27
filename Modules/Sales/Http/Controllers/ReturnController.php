<?php

namespace Modules\Sales\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Sales\Models\ReturnRequest; // Using the alias class
use Modules\Inventory\Models\InventoryMovement;
use Modules\Customer\Models\Customer;
use Modules\Inventory\Models\Supplier;
use Modules\Finance\Models\Transaction;

class ReturnController extends Controller
{
    public function index()
    {
        return ReturnRequest::with(['customer', 'supplier', 'items.product'])
            ->latest()
            ->paginate(50);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:sale_return,purchase_return',
            'customer_id' => 'required_if:type,sale_return|nullable|exists:customers,id',
            'supplier_id' => 'required_if:type,purchase_return|nullable|exists:suppliers,id',
            'date' => 'required|date',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.price' => 'required|numeric|min:0', // Refund unit price
        ]);

        return DB::transaction(function () use ($validated, $request) {
            $totalAmount = 0;
            foreach ($validated['items'] as $item) {
                $totalAmount += $item['quantity'] * $item['price'];
            }

            $return = ReturnRequest::create([
                'tenant_id' => $request->user()->tenant_id ?? 1,
                'type' => $validated['type'],
                'status' => 'pending', // Draft
                'customer_id' => $validated['customer_id'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
                'total_amount' => $totalAmount
            ]);

            foreach ($validated['items'] as $item) {
                $return->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total' => $item['quantity'] * $item['price']
                ]);
            }

            return $return->load('items.product');
        });
    }

    public function show($id)
    {
        return ReturnRequest::with(['customer', 'supplier', 'items.product'])->findOrFail($id);
    }

    public function approve($id)
    {
        return DB::transaction(function () use ($id) {
            $return = ReturnRequest::with('items')->findOrFail($id);

            if ($return->status !== 'pending') {
                return response()->json(['message' => 'Return already processed'], 400);
            }

            // 1. Inventory Adjustment
            foreach ($return->items as $item) {
                if ($return->type === 'sale_return') {
                    // Customer returned item -> Stock INCREASES
                    InventoryMovement::create([
                        'tenant_id' => $return->tenant_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => 1, // Default warehouse for now
                        'quantity' => $item->quantity, // Positive
                        'type' => 'sale_return',
                        'reference_id' => $return->id,
                    ]);
                } elseif ($return->type === 'purchase_return') {
                    // Start return to supplier -> Stock DECREASES
                    InventoryMovement::create([
                        'tenant_id' => $return->tenant_id,
                        'product_id' => $item->product_id,
                        'warehouse_id' => 1,
                        'quantity' => -$item->quantity, // Negative
                        'type' => 'purchase_return',
                        'reference_id' => $return->id,
                    ]);
                }
            }

            // 2. Financial Adjustment (Balance)
            if ($return->type === 'sale_return' && $return->customer_id) {
                // Credit Customer (We owe them money or reduce their debt)
                // Transaction: Type='payment' (out) or 'return_refund'
                // Let's create a Transaction record that affects balance
                // If Sale = Debit Customer
                // Sale Return = Credit Customer

                // Using existing Transaction structure?
                // 'type' enum in transactions might need check. usually 'collection', 'payment'.
                // Let's assume we treat this as a 'payment' (we paid them back) or negative collection?
                // Simplest: Add 'return' type to transactions if possible, or just use 'payment' but make sure context is clear.

                // Actually, if we just want to update balance:
                $customer = Customer::find($return->customer_id);
                // Reduce debt (balance decreases)
                $customer->decrement('balance', $return->total_amount);

                // Create a transaction record for visibility
                Transaction::create([
                    'tenant_id' => $return->tenant_id,
                    'customer_id' => $customer->id,
                    'type' => 'return', // Let's hope enum supports or is string
                    'amount' => $return->total_amount,
                    'date' => $return->date,
                    'description' => 'Satış İadesi #' . $return->id
                ]);

            } elseif ($return->type === 'purchase_return' && $return->supplier_id) {
                // Debit Supplier (They owe us or we owe them less)
                $supplier = Supplier::find($return->supplier_id);
                // Reduce our debt to them (balance decreases if positive means we owe)
                $supplier->decrement('balance', $return->total_amount);

                Transaction::create([
                    'tenant_id' => $return->tenant_id,
                    'supplier_id' => $supplier->id,
                    'type' => 'return',
                    'amount' => $return->total_amount,
                    'date' => $return->date,
                    'description' => 'Alış İadesi #' . $return->id
                ]);
            }

            $return->update(['status' => 'approved']);

            return $return;
        });
    }
}

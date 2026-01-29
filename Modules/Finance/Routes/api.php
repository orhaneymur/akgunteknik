<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\InvoiceController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/finance/invoices', [InvoiceController::class, 'index']);
    Route::post('/finance/invoices/from-source', [InvoiceController::class, 'storeFromSource']);
    Route::get('/finance/invoices/{id}', [InvoiceController::class, 'show']);

    Route::apiResource('finance/safes', \Modules\Finance\Http\Controllers\SafeController::class);
    Route::apiResource('finance/expense-categories', \Modules\Finance\Http\Controllers\ExpenseCategoryController::class);
    Route::apiResource('finance/expenses', \Modules\Finance\Http\Controllers\ExpenseController::class);
    Route::get('finance/transactions', [\Modules\Finance\Http\Controllers\TransactionController::class, 'index']);
    Route::post('finance/transactions', [\Modules\Finance\Http\Controllers\TransactionController::class, 'store']);
});

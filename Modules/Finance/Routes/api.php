<?php

use Illuminate\Support\Facades\Route;
use Modules\Finance\Http\Controllers\InvoiceController;

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Finans ekranlarına erişim: en az manager seviyesi (kasa, gider, hareketler kritik)
    Route::middleware('role:manager')->group(function () {
        Route::get('/finance/invoices', [InvoiceController::class, 'index']);
        Route::post('/finance/invoices/from-source', [InvoiceController::class, 'storeFromSource']);
        Route::get('/finance/invoices/{id}', [InvoiceController::class, 'show']);
        Route::get('/finance/invoices/{id}/pdf', [InvoiceController::class, 'viewPdf']);
        Route::get('/finance/invoices/{id}/download', [InvoiceController::class, 'downloadPdf']);

        Route::apiResource('finance/safes', \Modules\Finance\Http\Controllers\SafeController::class);
        Route::apiResource('finance/expense-categories', \Modules\Finance\Http\Controllers\ExpenseCategoryController::class);
        Route::apiResource('finance/expenses', \Modules\Finance\Http\Controllers\ExpenseController::class);
        Route::get('finance/transactions', [\Modules\Finance\Http\Controllers\TransactionController::class, 'index']);
        Route::post('finance/transactions', [\Modules\Finance\Http\Controllers\TransactionController::class, 'store']);
        
        // Payment management
        Route::apiResource('finance/payments', \Modules\Finance\Http\Controllers\PaymentController::class);
        Route::post('finance/payments/{id}/cancel', [\Modules\Finance\Http\Controllers\PaymentController::class, 'cancel']);
        
        // Due date tracking
        Route::get('finance/due-dates/overdue', [\Modules\Finance\Http\Controllers\DueDateController::class, 'overdueInvoices']);
        Route::get('finance/due-dates/due-soon', [\Modules\Finance\Http\Controllers\DueDateController::class, 'dueSoonInvoices']);
        Route::get('finance/due-dates/report', [\Modules\Finance\Http\Controllers\DueDateController::class, 'dueDateReport']);
        Route::get('finance/due-dates/customers/{customerId}/summary', [\Modules\Finance\Http\Controllers\DueDateController::class, 'customerPaymentSummary']);
    });
});

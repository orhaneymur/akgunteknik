<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\OrderController;

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Satış ve tekliflerle ilgili temel işlemler: tüm personel (staff)
    Route::middleware('role:staff')->group(function () {
        Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);
        Route::post('orders/{id}/status', [OrderController::class, 'updateStatus']); // Status güncelleme
        Route::apiResource('quotes', \Modules\Sales\Http\Controllers\QuoteController::class);
    });

    // Teklifi siparişe çevirme gibi daha kritik aksiyonlar: manager
    Route::middleware('role:manager')->group(function () {
        Route::post('quotes/{id}/convert', [\Modules\Sales\Http\Controllers\QuoteController::class, 'convertToOrder']);
    });
});

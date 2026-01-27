<?php

use Illuminate\Support\Facades\Route;
use Modules\Sales\Http\Controllers\OrderController;

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('orders', OrderController::class)->only(['index', 'store', 'show']);

    Route::apiResource('quotes', \Modules\Sales\Http\Controllers\QuoteController::class);
    Route::post('quotes/{id}/convert', [\Modules\Sales\Http\Controllers\QuoteController::class, 'convertToOrder']);
});

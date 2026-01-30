<?php

use Illuminate\Support\Facades\Route;
use Modules\Customer\Http\Controllers\CustomerController;

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Müşteri/cari kart işlemleri: tüm personel (staff) erişebilir
    Route::middleware('role:staff')->group(function () {
        Route::apiResource('customers', CustomerController::class);
        Route::get('customer-groups', [\Modules\Customer\Http\Controllers\CustomerGroupController::class, 'index']);
    });

    // Müşteri grup yönetimi: manager seviyesi
    Route::middleware('role:manager')->group(function () {
        Route::apiResource('customer-groups', \Modules\Customer\Http\Controllers\CustomerGroupController::class)->except(['index']);
    });
});

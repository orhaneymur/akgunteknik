<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\UserController;
use Modules\Core\Http\Controllers\WarehouseController;
use Modules\Core\Http\Controllers\ReportController;
use Modules\Core\Http\Controllers\TaxRateController;

/*
|--------------------------------------------------------------------------
| Core Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Core module.
| These routes are loaded by the CoreServiceProvider.
|
*/

// Public routes with rate limiting
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/core/login', [AuthController::class, 'login']);
});

// Authenticated routes with rate limiting
Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    Route::post('/core/logout', [AuthController::class, 'logout']);
});

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Genel dashboard ve raporlar - giriş yapmış tüm personel erişebilir (staff seviyesi)
    Route::middleware('role:staff')->group(function () {
        Route::get('/core/dashboard/stats', [DashboardController::class, 'index']);
        Route::get('/core/reports/dashboard-stats', [ReportController::class, 'dashboardStats']);
        Route::get('/core/reports/sales', [ReportController::class, 'salesReport']);
        Route::get('/core/reports/stock', [ReportController::class, 'stockReport']);
        Route::get('/core/warehouses', [WarehouseController::class, 'index']); // dropdownlar için
        Route::get('/core/tax-rates', [TaxRateController::class, 'index']); // KDV oranları için
        Route::get('/core/exchange-rates', [\Modules\Core\Http\Controllers\ExchangeRateController::class, 'index']); // Döviz kurları
        Route::get('/core/exchange-rates/latest/{currency}', [\Modules\Core\Http\Controllers\ExchangeRateController::class, 'getLatest']); // Son kur
    });

    // Döviz kuru ekleme/güncelleme - manager seviyesi
    Route::middleware('role:manager')->group(function () {
        Route::post('/core/exchange-rates', [\Modules\Core\Http\Controllers\ExchangeRateController::class, 'store']); // Döviz kuru ekle/güncelle
    });

    // Kullanıcı yönetimi sadece tenant sahibi (owner) tarafından yapılabilir
    Route::middleware('role:owner')->group(function () {
        Route::apiResource('/core/users', UserController::class);
    });
});

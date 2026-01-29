<?php

use Illuminate\Support\Facades\Route;
use Modules\Core\Http\Controllers\AuthController;
use Modules\Core\Http\Controllers\DashboardController;
use Modules\Core\Http\Controllers\UserController;
use Modules\Core\Http\Controllers\WarehouseController;
use Modules\Core\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| Core Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Core module.
| These routes are loaded by the CoreServiceProvider.
|
*/

Route::post('/core/login', [AuthController::class, 'login']);
Route::middleware(['auth:sanctum'])->post('/core/logout', [AuthController::class, 'logout']);

Route::middleware(['auth:sanctum'])->group(function () {
    // Genel dashboard ve raporlar - giriş yapmış tüm personel erişebilir (staff seviyesi)
    Route::middleware('role:staff')->group(function () {
        Route::get('/core/dashboard/stats', [DashboardController::class, 'index']);
        Route::get('/core/reports/dashboard-stats', [ReportController::class, 'dashboardStats']);
        Route::get('/core/reports/sales', [ReportController::class, 'salesReport']);
        Route::get('/core/reports/stock', [ReportController::class, 'stockReport']);
        Route::get('/core/warehouses', [WarehouseController::class, 'index']); // dropdownlar için
    });

    // Kullanıcı yönetimi sadece tenant sahibi (owner) tarafından yapılabilir
    Route::middleware('role:owner')->group(function () {
        Route::apiResource('/core/users', UserController::class);
    });
});

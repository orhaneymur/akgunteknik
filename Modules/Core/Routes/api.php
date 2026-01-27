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

Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/core/dashboard/stats', [DashboardController::class, 'index']);
    Route::get('/core/reports/dashboard-stats', [ReportController::class, 'dashboardStats']);
    Route::get('/core/reports/sales', [ReportController::class, 'salesReport']);
    Route::get('/core/reports/stock', [ReportController::class, 'stockReport']);

    // Legacy dashboard route if needed, otherwise replace
    // Route::get('/dashboard', ...); 
    Route::apiResource('/core/users', UserController::class);
    Route::get('/core/warehouses', [WarehouseController::class, 'index']); // Need this for dropdowns
});

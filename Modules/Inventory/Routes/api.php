<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\ProductController;
use Modules\Inventory\Http\Controllers\StockController;

/*
|--------------------------------------------------------------------------
| Inventory Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the Inventory module.
| These routes are loaded by the InventoryServiceProvider.
|
*/

Route::middleware('auth:sanctum')->group(function () {
    // Product routes
    Route::get('/inventory/products', [ProductController::class, 'index']);
    Route::post('/inventory/products', [ProductController::class, 'store']);
    
    // Stock routes
    Route::post('/inventory/stock/add', [StockController::class, 'addStock']);
});


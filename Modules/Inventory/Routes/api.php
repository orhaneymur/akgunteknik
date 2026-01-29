<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\ProductController;
use Modules\Inventory\Http\Controllers\InventoryMovementController;
use Modules\Inventory\Http\Controllers\SupplierController;
use Modules\Inventory\Http\Controllers\PurchaseOrderController;

Route::middleware(['auth:sanctum'])->group(function () {
    // Günlük stok ve ürün işlemleri: tüm personel (staff) erişebilir
    Route::middleware('role:staff')->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::get('movements', [InventoryMovementController::class, 'index']);
        Route::apiResource('suppliers', SupplierController::class);
    });

    // Daha kritik stok hareketleri: en az manager seviyesi
    Route::middleware('role:manager')->group(function () {
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);

        Route::apiResource('inventory-transfers', \Modules\Inventory\Http\Controllers\InventoryTransferController::class);
        Route::post('inventory-transfers/{id}/complete', [\Modules\Inventory\Http\Controllers\InventoryTransferController::class, 'complete']);
        Route::post('inventory-transfers/{id}/cancel', [\Modules\Inventory\Http\Controllers\InventoryTransferController::class, 'cancel']);

        Route::apiResource('returns', \Modules\Sales\Http\Controllers\ReturnController::class);
        Route::post('returns/{id}/approve', [\Modules\Sales\Http\Controllers\ReturnController::class, 'approve']);
    });
});

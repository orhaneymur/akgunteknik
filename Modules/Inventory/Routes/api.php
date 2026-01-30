<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\ProductController;
use Modules\Inventory\Http\Controllers\InventoryMovementController;
use Modules\Inventory\Http\Controllers\SupplierController;
use Modules\Inventory\Http\Controllers\PurchaseOrderController;

Route::middleware(['auth:sanctum', 'throttle:120,1'])->group(function () {
    // Günlük stok ve ürün işlemleri: tüm personel (staff) erişebilir
    Route::middleware('role:staff')->group(function () {
        Route::apiResource('products', ProductController::class);
        Route::get('movements', [InventoryMovementController::class, 'index']);
        Route::apiResource('suppliers', SupplierController::class);
        Route::get('price-lists', [\Modules\Inventory\Http\Controllers\PriceListController::class, 'index']);
        
        // Product categories, brands, models - Full CRUD for staff
        Route::apiResource('product-categories', \Modules\Inventory\Http\Controllers\ProductCategoryController::class);
        Route::apiResource('brands', \Modules\Inventory\Http\Controllers\BrandController::class);
        Route::apiResource('product-models', \Modules\Inventory\Http\Controllers\ProductModelController::class);
    });

    // Daha kritik stok hareketleri: en az manager seviyesi
    Route::middleware('role:manager')->group(function () {
        Route::apiResource('purchase-orders', PurchaseOrderController::class);
        Route::post('purchase-orders/{id}/receive', [PurchaseOrderController::class, 'receive']);
        
        // Import costs management
        Route::get('purchase-orders/{purchaseOrderId}/import-costs', [\Modules\Inventory\Http\Controllers\ImportCostController::class, 'index']);
        Route::post('purchase-orders/{purchaseOrderId}/import-costs', [\Modules\Inventory\Http\Controllers\ImportCostController::class, 'store']);
        Route::put('purchase-orders/{purchaseOrderId}/import-costs/{id}', [\Modules\Inventory\Http\Controllers\ImportCostController::class, 'update']);
        Route::delete('purchase-orders/{purchaseOrderId}/import-costs/{id}', [\Modules\Inventory\Http\Controllers\ImportCostController::class, 'destroy']);

        Route::apiResource('inventory-transfers', \Modules\Inventory\Http\Controllers\InventoryTransferController::class);
        Route::post('inventory-transfers/{id}/complete', [\Modules\Inventory\Http\Controllers\InventoryTransferController::class, 'complete']);
        Route::post('inventory-transfers/{id}/cancel', [\Modules\Inventory\Http\Controllers\InventoryTransferController::class, 'cancel']);

        Route::apiResource('returns', \Modules\Sales\Http\Controllers\ReturnController::class);
        Route::post('returns/{id}/approve', [\Modules\Sales\Http\Controllers\ReturnController::class, 'approve']);

        // Price list management
        Route::apiResource('price-lists', \Modules\Inventory\Http\Controllers\PriceListController::class)->except(['index']);
        Route::post('price-lists/{id}/products', [\Modules\Inventory\Http\Controllers\PriceListController::class, 'addProduct']);
        Route::post('price-lists/{id}/bulk-update', [\Modules\Inventory\Http\Controllers\PriceListController::class, 'bulkUpdate']);

        // Product categories, brands, models management - Moved to staff group above
        // These routes are now accessible to staff role
    });
});

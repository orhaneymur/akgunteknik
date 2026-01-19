<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('tenant_id');
            $table->string('name');
            $table->string('sku'); // Stock Keeping Unit
            $table->string('barcode')->nullable();
            $table->decimal('purchase_price', 10, 2);
            $table->decimal('sale_price', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->json('compatibility')->nullable(); // Store compatible models like ["iPhone 11", "iPhone XR"]
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign key to tenants
            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            
            // Indexes
            $table->index('tenant_id');
            
            // Unique constraint: SKU must be unique per tenant
            $table->unique(['tenant_id', 'sku'], 'products_tenant_sku_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('price', 15, 2); // Bu fiyat listesindeki ürün fiyatı
            $table->integer('min_quantity')->default(1); // Minimum miktar (miktar bazlı fiyatlandırma için)
            $table->integer('max_quantity')->nullable(); // Maksimum miktar (null = sınırsız)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Aynı fiyat listesinde aynı ürün için miktar aralığı benzersiz olmalı
            $table->unique(['price_list_id', 'product_id', 'min_quantity']);
            $table->index('price_list_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_price_lists');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('tenant_id')
                ->constrained('product_categories')->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->after('category_id')
                ->constrained('brands')->nullOnDelete();
            $table->foreignId('model_id')->nullable()->after('brand_id')
                ->constrained('product_models')->nullOnDelete();
            
            $table->index('category_id');
            $table->index('brand_id');
            $table->index('model_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['brand_id']);
            $table->dropForeign(['model_id']);
            $table->dropColumn(['category_id', 'brand_id', 'model_id']);
        });
    }
};

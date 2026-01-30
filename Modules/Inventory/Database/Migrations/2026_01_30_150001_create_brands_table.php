<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Samsung, Apple, Huawei, Xiaomi vb.
            $table->string('code')->nullable(); // Unique code per tenant
            $table->string('logo')->nullable(); // Logo dosya yolu
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index('tenant_id');
        });

        // Insert default brands for existing tenants
        $defaultBrands = [
            ['name' => 'Samsung', 'code' => 'SAMSUNG', 'sort_order' => 1],
            ['name' => 'Apple', 'code' => 'APPLE', 'sort_order' => 2],
            ['name' => 'Huawei', 'code' => 'HUAWEI', 'sort_order' => 3],
            ['name' => 'Xiaomi', 'code' => 'XIAOMI', 'sort_order' => 4],
            ['name' => 'Oppo', 'code' => 'OPPO', 'sort_order' => 5],
            ['name' => 'Vivo', 'code' => 'VIVO', 'sort_order' => 6],
            ['name' => 'OnePlus', 'code' => 'ONEPLUS', 'sort_order' => 7],
            ['name' => 'Realme', 'code' => 'REALME', 'sort_order' => 8],
            ['name' => 'Diğer', 'code' => 'OTHER', 'sort_order' => 99],
        ];

        $tenants = DB::table('tenants')->pluck('id');
        foreach ($tenants as $tenantId) {
            foreach ($defaultBrands as $brand) {
                DB::table('brands')->insert([
                    'tenant_id' => $tenantId,
                    'name' => $brand['name'],
                    'code' => $brand['code'],
                    'is_active' => true,
                    'sort_order' => $brand['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};

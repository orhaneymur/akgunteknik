<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('product_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Ekran, Batarya, Kamera, Hoparlör vb.
            $table->string('code')->nullable(); // Unique code per tenant
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index('tenant_id');
        });

        // Insert default categories for existing tenants
        $defaultCategories = [
            ['name' => 'Ekran', 'code' => 'DISPLAY', 'sort_order' => 1],
            ['name' => 'Batarya', 'code' => 'BATTERY', 'sort_order' => 2],
            ['name' => 'Kamera', 'code' => 'CAMERA', 'sort_order' => 3],
            ['name' => 'Hoparlör', 'code' => 'SPEAKER', 'sort_order' => 4],
            ['name' => 'Şarj Portu', 'code' => 'CHARGING_PORT', 'sort_order' => 5],
            ['name' => 'Ana Kart', 'code' => 'MOTHERBOARD', 'sort_order' => 6],
            ['name' => 'Kulaklık Jakı', 'code' => 'HEADPHONE_JACK', 'sort_order' => 7],
            ['name' => 'Buton', 'code' => 'BUTTON', 'sort_order' => 8],
            ['name' => 'Kasa', 'code' => 'HOUSING', 'sort_order' => 9],
            ['name' => 'Diğer', 'code' => 'OTHER', 'sort_order' => 10],
        ];

        $tenants = DB::table('tenants')->pluck('id');
        foreach ($tenants as $tenantId) {
            foreach ($defaultCategories as $category) {
                DB::table('product_categories')->insert([
                    'tenant_id' => $tenantId,
                    'name' => $category['name'],
                    'code' => $category['code'],
                    'is_active' => true,
                    'sort_order' => $category['sort_order'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('product_categories');
    }
};

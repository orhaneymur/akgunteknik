<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "KDV %20", "KDV %10"
            $table->decimal('rate', 5, 2); // e.g., 20.00, 10.00, 1.00, 0.00
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0); // For ordering in dropdowns
            $table->timestamps();
        });

        // Insert default tax rates for Turkey
        DB::table('tax_rates')->insert([
            ['name' => 'KDV %0', 'rate' => 0.00, 'is_active' => true, 'sort_order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KDV %1', 'rate' => 1.00, 'is_active' => true, 'sort_order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KDV %10', 'rate' => 10.00, 'is_active' => true, 'sort_order' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'KDV %20', 'rate' => 20.00, 'is_active' => true, 'sort_order' => 4, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('tax_rates');
    }
};

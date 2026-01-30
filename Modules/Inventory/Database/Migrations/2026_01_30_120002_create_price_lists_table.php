<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // Toptancı, Perakende, Özel Liste vb.
            $table->string('code')->nullable(); // Unique code per tenant
            $table->enum('type', ['wholesale', 'retail', 'custom'])->default('custom');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // Varsayılan fiyat listesi
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index('tenant_id');
        });

        // Insert default price lists for existing tenants
        DB::statement("
            INSERT INTO price_lists (tenant_id, name, code, type, is_active, is_default, sort_order, created_at, updated_at)
            SELECT id, 'Toptancı Fiyat Listesi', 'WHOLESALE', 'wholesale', true, true, 1, NOW(), NOW() FROM tenants
        ");
        DB::statement("
            INSERT INTO price_lists (tenant_id, name, code, type, is_active, is_default, sort_order, created_at, updated_at)
            SELECT id, 'Perakende Fiyat Listesi', 'RETAIL', 'retail', true, false, 2, NOW(), NOW() FROM tenants
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('price_lists');
    }
};

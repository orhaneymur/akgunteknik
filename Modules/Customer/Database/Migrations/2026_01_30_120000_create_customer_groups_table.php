<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name'); // VIP, Standart, Yeni, vb.
            $table->string('code')->nullable(); // Unique code per tenant
            $table->decimal('discount_percentage', 5, 2)->default(0); // Grup bazlı indirim %
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'code']);
            $table->index('tenant_id');
        });

        // Insert default groups for existing tenants
        DB::statement("
            INSERT INTO customer_groups (tenant_id, name, code, discount_percentage, is_active, sort_order, created_at, updated_at)
            SELECT id, 'VIP', 'VIP', 10.00, true, 1, NOW(), NOW() FROM tenants
        ");
        DB::statement("
            INSERT INTO customer_groups (tenant_id, name, code, discount_percentage, is_active, sort_order, created_at, updated_at)
            SELECT id, 'Standart', 'STANDARD', 0.00, true, 2, NOW(), NOW() FROM tenants
        ");
        DB::statement("
            INSERT INTO customer_groups (tenant_id, name, code, discount_percentage, is_active, sort_order, created_at, updated_at)
            SELECT id, 'Yeni Müşteri', 'NEW', 5.00, true, 3, NOW(), NOW() FROM tenants
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_groups');
    }
};

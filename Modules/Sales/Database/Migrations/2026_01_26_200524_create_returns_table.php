<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('type'); // sale_return, purchase_return
            $table->string('status')->default('pending'); // pending, approved, rejected

            // Link to original transaction (optional)
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('purchase_order_id')->nullable();

            // Contact (Customer or Supplier) - Polymorphic or just ID? 
            // Let's use specific IDs for simplicity or just contact_id with type check in logic.
            // Actually, best practice: customer_id and supplier_id nullable.
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->unsignedBigInteger('supplier_id')->nullable();

            $table->decimal('total_amount', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->date('date');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};

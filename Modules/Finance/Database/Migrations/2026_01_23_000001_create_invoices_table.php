<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            // Polymorphic relation to link to Order or PurchaseOrder
            $table->nullableMorphs('invoiceable');
            $table->string('invoice_number')->unique(); // e.g., INV-2024-0001
            $table->string('contact_name'); // Customer or Supplier Name
            $table->date('issue_date');
            $table->date('due_date')->nullable();
            $table->decimal('total_amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->string('status')->default('draft'); // draft, sent, paid, cancelled
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnUpdate()->cascadeOnDelete();
            $table->index('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Payment source (Invoice or Order)
            $table->string('payable_type'); // Modules\Finance\Models\Invoice, Modules\Sales\Models\Order
            $table->unsignedBigInteger('payable_id');
            $table->index(['payable_type', 'payable_id']);
            
            // Customer who made the payment
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            
            // Payment details
            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['cash', 'credit_card', 'bank_transfer', 'check', 'other'])->default('cash');
            $table->string('reference_number')->nullable(); // Çek no, havale no, kart işlem no vb.
            $table->text('notes')->nullable();
            
            // Safe where payment was received
            $table->foreignId('safe_id')->nullable()->constrained('safes')->nullOnDelete();
            
            // Status
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
            
            // Who recorded this payment
            $table->unsignedBigInteger('recorded_by')->nullable();
            
            $table->timestamps();
            
            $table->index('tenant_id');
            $table->index('customer_id');
            $table->index('payment_date');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();

            // Who is this transaction for? (Customer, Supplier, ExpenseCategory...)
            $table->string('payable_type')->nullable();
            $table->unsignedBigInteger('payable_id')->nullable();
            $table->index(['payable_type', 'payable_id']);

            // Which safe was affected? (Nullable if it's just an accrual)
            $table->unsignedBigInteger('safe_id')->nullable();
            $table->foreign('safe_id')->references('id')->on('safes')->onDelete('cascade');

            $table->string('type'); // deposit, withdrawal
            $table->decimal('amount', 15, 2);
            $table->string('currency')->default('TRY');
            $table->string('description')->nullable();
            $table->date('date');

            // What triggered this? (Invoice, Payment, Expense...)
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->index(['reference_type', 'reference_id']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

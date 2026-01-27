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
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('description');
            $table->decimal('amount', 15, 2);
            $table->date('date');

            $table->unsignedBigInteger('category_id');
            $table->foreign('category_id')->references('id')->on('expense_categories')->onDelete('cascade');

            $table->unsignedBigInteger('safe_id')->nullable(); // Paid from which safe?
            $table->foreign('safe_id')->references('id')->on('safes')->onDelete('set null');

            $table->string('document_path')->nullable(); // Receipt/Invoice image
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

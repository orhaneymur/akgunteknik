<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('import_costs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->string('cost_type'); // customs, tax, freight, insurance, other
            $table->string('description')->nullable(); // Açıklama
            $table->decimal('amount', 15, 2); // Tutar (TL cinsinden)
            $table->string('currency', 3)->default('TRY'); // Para birimi
            $table->date('cost_date')->nullable(); // Masraf tarihi
            $table->text('notes')->nullable(); // Notlar
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->cascadeOnDelete();
            $table->index('purchase_order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('import_costs');
    }
};

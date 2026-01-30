<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('currency', 3); // USD, EUR, GBP, etc.
            $table->date('rate_date'); // Tarih bazlı kur takibi
            $table->decimal('rate', 10, 4); // 1 USD = X TL (örn: 34.2500)
            $table->text('notes')->nullable(); // Opsiyonel notlar
            $table->timestamps();

            // Aynı tarih ve döviz için tek kayıt
            $table->unique(['currency', 'rate_date']);
            $table->index('rate_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->string('currency', 3)->default('TRY')->after('total_amount'); // TRY, USD, EUR, GBP
            $table->decimal('exchange_rate', 10, 4)->nullable()->after('currency'); // 1 döviz = X TL
            $table->decimal('total_amount_tl', 15, 2)->default(0)->after('exchange_rate'); // TL karşılığı
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropColumn(['currency', 'exchange_rate', 'total_amount_tl']);
        });
    }
};

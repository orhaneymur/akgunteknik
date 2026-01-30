<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('customer_type', ['b2b', 'b2c'])->default('b2b')->after('name');
            // b2b = Toptancı/Kurumsal (Vergi no zorunlu, ticari fatura)
            // b2c = Perakende (Vergi no opsiyonel, basit fatura)
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('customer_type');
        });
    }
};

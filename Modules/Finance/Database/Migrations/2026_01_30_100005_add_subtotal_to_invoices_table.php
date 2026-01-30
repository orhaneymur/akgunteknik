<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 15, 2)->default(0)->after('total_amount');
            // subtotal_amount = sum of all items (without tax)
            // tax_amount = sum of all taxes
            // total_amount = subtotal_amount + tax_amount
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('subtotal_amount');
        });
    }
};

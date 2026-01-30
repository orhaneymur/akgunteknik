<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('invoice_series')->default('FAT')->after('invoice_number'); // FAT, PER, etc.
            $table->integer('invoice_number_sequence')->nullable()->after('invoice_series'); // Sequential number part
            // Full invoice number will be: {series}-{year}{month}{day}-{sequence}
            // e.g., FAT-20260130-0001
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_series', 'invoice_number_sequence']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('customer_group_id')->nullable()->after('customer_type')
                ->constrained('customer_groups')->nullOnDelete();
        });

        // Set default group (Standart) for existing customers
        DB::statement("
            UPDATE customers 
            SET customer_group_id = (
                SELECT id FROM customer_groups 
                WHERE customer_groups.tenant_id = customers.tenant_id 
                AND customer_groups.code = 'STANDARD' 
                LIMIT 1
            )
            WHERE customer_group_id IS NULL
        ");
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['customer_group_id']);
            $table->dropColumn('customer_group_id');
        });
    }
};

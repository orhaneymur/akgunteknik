<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('paid_amount', 15, 2)->default(0)->after('total_amount');
            $table->decimal('remaining_amount', 15, 2)->default(0)->after('paid_amount');
            $table->date('paid_at')->nullable()->after('remaining_amount');
        });

        // Calculate initial remaining amounts for existing orders
        DB::statement("UPDATE orders SET remaining_amount = total_amount WHERE remaining_amount = 0");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['paid_amount', 'remaining_amount', 'paid_at']);
        });
    }
};

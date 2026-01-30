<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            // Change status enum to include new statuses
            $table->string('status')->default('pending')->change();
            // New statuses: pending, processing, shipped, delivered, cancelled
            
            // Add order number
            $table->string('order_number')->nullable()->after('id');
            
            // Add shipping information
            $table->text('shipping_address')->nullable()->after('customer_id');
            $table->string('carrier')->nullable()->after('shipping_address'); // Kargo firması
            $table->string('tracking_number')->nullable()->after('carrier');
            
            // Add status timestamps
            $table->timestamp('processing_at')->nullable()->after('completed_at');
            $table->timestamp('shipped_at')->nullable()->after('processing_at');
            $table->timestamp('delivered_at')->nullable()->after('shipped_at');
            $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
            
            $table->index('order_number');
        });

        // Update existing orders: if status is 'completed', set to 'delivered'
        DB::statement("UPDATE orders SET status = 'delivered' WHERE status = 'completed'");
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'order_number',
                'shipping_address',
                'carrier',
                'tracking_number',
                'processing_at',
                'shipped_at',
                'delivered_at',
                'cancelled_at',
            ]);
            
            // Revert status to enum
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending')->change();
        });
    }
};

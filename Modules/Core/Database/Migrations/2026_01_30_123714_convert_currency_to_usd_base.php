<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Convert all TRY amounts to USD
     * This migration assumes all existing data is in TRY
     * and converts it to USD using the latest exchange rate
     */
    public function up(): void
    {
        // Get latest USD to TRY rate (we need to convert TRY to USD, so we divide)
        $rateRecord = DB::table('exchange_rates')
            ->where('currency', 'USD')
            ->orderBy('rate_date', 'desc')
            ->first();
        
        $usdToTryRate = $rateRecord ? (float) $rateRecord->rate : null;
        
        if (!$usdToTryRate || $usdToTryRate <= 0) {
            // If no rate found, set default rate (e.g., 34.00)
            // This should be updated manually after migration
            $usdToTryRate = 34.00;
            Log::warning('No USD exchange rate found. Using default rate: ' . $usdToTryRate);
        }

        // Convert products prices (base_price, cost_price)
        if (Schema::hasTable('products')) {
            DB::statement("UPDATE products SET base_price = base_price / {$usdToTryRate} WHERE base_price > 0");
            if (Schema::hasColumn('products', 'cost_price')) {
                DB::statement("UPDATE products SET cost_price = cost_price / {$usdToTryRate} WHERE cost_price > 0");
            }
        }

        // Convert order amounts
        if (Schema::hasTable('orders')) {
            DB::statement("UPDATE orders SET total_amount = total_amount / {$usdToTryRate} WHERE total_amount > 0");
            if (Schema::hasColumn('orders', 'paid_amount')) {
                DB::statement("UPDATE orders SET paid_amount = paid_amount / {$usdToTryRate} WHERE paid_amount > 0");
            }
            if (Schema::hasColumn('orders', 'remaining_amount')) {
                DB::statement("UPDATE orders SET remaining_amount = remaining_amount / {$usdToTryRate} WHERE remaining_amount > 0");
            }
        }

        // Convert invoice amounts
        if (Schema::hasTable('invoices')) {
            DB::statement("UPDATE invoices SET total_amount = total_amount / {$usdToTryRate} WHERE total_amount > 0");
            if (Schema::hasColumn('invoices', 'tax_amount')) {
                DB::statement("UPDATE invoices SET tax_amount = tax_amount / {$usdToTryRate} WHERE tax_amount > 0");
            }
            if (Schema::hasColumn('invoices', 'subtotal_amount')) {
                DB::statement("UPDATE invoices SET subtotal_amount = subtotal_amount / {$usdToTryRate} WHERE subtotal_amount > 0");
            }
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                DB::statement("UPDATE invoices SET paid_amount = paid_amount / {$usdToTryRate} WHERE paid_amount > 0");
            }
            if (Schema::hasColumn('invoices', 'remaining_amount')) {
                DB::statement("UPDATE invoices SET remaining_amount = remaining_amount / {$usdToTryRate} WHERE remaining_amount > 0");
            }
        }

        // Convert order items
        if (Schema::hasTable('order_items')) {
            DB::statement("UPDATE order_items SET unit_price = unit_price / {$usdToTryRate} WHERE unit_price > 0");
            DB::statement("UPDATE order_items SET total_price = total_price / {$usdToTryRate} WHERE total_price > 0");
        }

        // Convert invoice items
        if (Schema::hasTable('invoice_items')) {
            DB::statement("UPDATE invoice_items SET unit_price = unit_price / {$usdToTryRate} WHERE unit_price > 0");
            DB::statement("UPDATE invoice_items SET total = total / {$usdToTryRate} WHERE total > 0");
        }

        // Convert purchase orders (already has currency support, but total_amount_tl needs conversion)
        if (Schema::hasTable('purchase_orders')) {
            // If currency is TRY, convert to USD
            DB::statement("UPDATE purchase_orders SET total_amount = total_amount / {$usdToTryRate}, currency = 'USD', exchange_rate = {$usdToTryRate} WHERE currency = 'TRY' OR currency IS NULL");
            // Update total_amount_tl to reflect USD * rate
            DB::statement("UPDATE purchase_orders SET total_amount_tl = total_amount * exchange_rate WHERE currency = 'USD' AND exchange_rate IS NOT NULL");
        }

        // Convert expenses
        if (Schema::hasTable('expenses')) {
            DB::statement("UPDATE expenses SET amount = amount / {$usdToTryRate} WHERE amount > 0");
        }

        // Convert transactions
        if (Schema::hasTable('transactions')) {
            DB::statement("UPDATE transactions SET amount = amount / {$usdToTryRate} WHERE amount > 0");
        }

        // Convert safes
        if (Schema::hasTable('safes')) {
            DB::statement("UPDATE safes SET balance = balance / {$usdToTryRate} WHERE balance > 0 AND currency = 'TRY'");
            DB::statement("UPDATE safes SET currency = 'USD' WHERE currency = 'TRY' OR currency IS NULL");
        }

        // Convert suppliers balance
        if (Schema::hasTable('suppliers')) {
            if (Schema::hasColumn('suppliers', 'balance')) {
                DB::statement("UPDATE suppliers SET balance = balance / {$usdToTryRate} WHERE balance > 0");
            }
        }

        // Convert price lists
        if (Schema::hasTable('product_price_lists')) {
            DB::statement("UPDATE product_price_lists SET price = price / {$usdToTryRate} WHERE price > 0");
        }
    }

    /**
     * Reverse the migration - convert USD back to TRY
     */
    public function down(): void
    {
        $rateRecord = DB::table('exchange_rates')
            ->where('currency', 'USD')
            ->orderBy('rate_date', 'desc')
            ->first();
        
        $usdToTryRate = $rateRecord ? (float) $rateRecord->rate : 34.00;

        // Reverse all conversions
        if (Schema::hasTable('products')) {
            DB::statement("UPDATE products SET base_price = base_price * {$usdToTryRate} WHERE base_price > 0");
            if (Schema::hasColumn('products', 'cost_price')) {
                DB::statement("UPDATE products SET cost_price = cost_price * {$usdToTryRate} WHERE cost_price > 0");
            }
        }

        if (Schema::hasTable('orders')) {
            DB::statement("UPDATE orders SET total_amount = total_amount * {$usdToTryRate} WHERE total_amount > 0");
            if (Schema::hasColumn('orders', 'paid_amount')) {
                DB::statement("UPDATE orders SET paid_amount = paid_amount * {$usdToTryRate} WHERE paid_amount > 0");
            }
            if (Schema::hasColumn('orders', 'remaining_amount')) {
                DB::statement("UPDATE orders SET remaining_amount = remaining_amount * {$usdToTryRate} WHERE remaining_amount > 0");
            }
        }

        if (Schema::hasTable('invoices')) {
            DB::statement("UPDATE invoices SET total_amount = total_amount * {$usdToTryRate} WHERE total_amount > 0");
            if (Schema::hasColumn('invoices', 'tax_amount')) {
                DB::statement("UPDATE invoices SET tax_amount = tax_amount * {$usdToTryRate} WHERE tax_amount > 0");
            }
            if (Schema::hasColumn('invoices', 'subtotal_amount')) {
                DB::statement("UPDATE invoices SET subtotal_amount = subtotal_amount * {$usdToTryRate} WHERE subtotal_amount > 0");
            }
            if (Schema::hasColumn('invoices', 'paid_amount')) {
                DB::statement("UPDATE invoices SET paid_amount = paid_amount * {$usdToTryRate} WHERE paid_amount > 0");
            }
            if (Schema::hasColumn('invoices', 'remaining_amount')) {
                DB::statement("UPDATE invoices SET remaining_amount = remaining_amount * {$usdToTryRate} WHERE remaining_amount > 0");
            }
        }

        if (Schema::hasTable('order_items')) {
            DB::statement("UPDATE order_items SET unit_price = unit_price * {$usdToTryRate} WHERE unit_price > 0");
            DB::statement("UPDATE order_items SET total_price = total_price * {$usdToTryRate} WHERE total_price > 0");
        }

        if (Schema::hasTable('invoice_items')) {
            DB::statement("UPDATE invoice_items SET unit_price = unit_price * {$usdToTryRate} WHERE unit_price > 0");
            DB::statement("UPDATE invoice_items SET total = total * {$usdToTryRate} WHERE total > 0");
        }

        if (Schema::hasTable('expenses')) {
            DB::statement("UPDATE expenses SET amount = amount * {$usdToTryRate} WHERE amount > 0");
        }

        if (Schema::hasTable('transactions')) {
            DB::statement("UPDATE transactions SET amount = amount * {$usdToTryRate} WHERE amount > 0");
        }

        if (Schema::hasTable('safes')) {
            DB::statement("UPDATE safes SET balance = balance * {$usdToTryRate} WHERE balance > 0 AND currency = 'USD'");
            DB::statement("UPDATE safes SET currency = 'TRY' WHERE currency = 'USD'");
        }

        if (Schema::hasTable('suppliers')) {
            if (Schema::hasColumn('suppliers', 'balance')) {
                DB::statement("UPDATE suppliers SET balance = balance * {$usdToTryRate} WHERE balance > 0");
            }
        }

        if (Schema::hasTable('product_price_lists')) {
            DB::statement("UPDATE product_price_lists SET price = price * {$usdToTryRate} WHERE price > 0");
        }
    }
};

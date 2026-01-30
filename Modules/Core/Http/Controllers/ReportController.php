<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Models\Order;
use Modules\Sales\Models\OrderItem;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\InventoryMovement;
use Modules\Finance\Models\Expense;

class ReportController extends BaseController
{
    /**
     * Get Main Dashboard Stats (Cards)
     */
    public function dashboardStats(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Total Sales (This Month) - using created_at since there's no date column
        $salesTotal = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->sum('total_amount') ?? 0;

        // 2. Total Expenses (This Month)
        $expensesTotal = Expense::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount') ?? 0;

        // 3. Profit (Sales - Expenses)
        // Note: For real Net Profit, we need COGS (Cost of Goods Sold)
        $profit = $salesTotal - $expensesTotal;

        // 4. Low Stock Count (using inventory movements to calculate current stock)
        $lowStockCount = Product::where('tenant_id', $tenantId)
            ->withSum('inventoryMovements as current_stock', 'quantity')
            ->get()
            ->filter(function ($product) {
                return ($product->current_stock ?? 0) < 10; // Critical level threshold
            })
            ->count();

        // 5. Open Orders
        $openOrders = Order::where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        // 6. Chart Data (Last 7 Days Sales)
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->startOfDay();
            $nextDate = $date->copy()->endOfDay();
            $dailySales = Order::where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$date, $nextDate])
                ->sum('total_amount') ?? 0;
            $last7Days->push([
                'date' => $date->format('Y-m-d'),
                'total' => $dailySales
            ]);
        }

        return $this->respondSuccess([
            'sales_month' => $salesTotal,
            'expenses_month' => $expensesTotal,
            'profit_month' => $profit,
            'low_stock_count' => $lowStockCount,
            'open_orders_count' => $openOrders,
            'daily_sales' => $last7Days
        ], 'Dashboard stats retrieved successfully.');
    }

    /**
     * Detailed Sales Report
     */
    public function salesReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        // Group by Date (using created_at)
        $salesByDate = Order::where('tenant_id', $tenantId)
            ->whereBetween('created_at', [$startDateTime, $endDateTime])
            ->selectRaw('DATE(created_at) as date, count(*) as count, sum(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Selling Products in Range
        $topProducts = OrderItem::whereHas('order', function ($q) use ($tenantId, $startDateTime, $endDateTime) {
            $q->where('tenant_id', $tenantId)
                ->whereBetween('created_at', [$startDateTime, $endDateTime]);
        })
            ->selectRaw('product_id, sum(quantity) as total_qty, sum(total_price) as total_revenue')
            ->groupBy('product_id')
            ->with('product:id,name,sku')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return $this->respondSuccess([
            'sales_by_date' => $salesByDate,
            'top_products' => $topProducts
        ], 'Sales report retrieved successfully.');
    }

    /**
     * Detailed Stock Report
     */
    public function stockReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Get all products with current stock calculated from inventory movements
        $products = Product::where('tenant_id', $tenantId)
            ->withSum('inventoryMovements as current_stock', 'quantity')
            ->get();

        // 1. Critical Stock (current_stock < 10)
        $criticalStock = $products->filter(function ($product) {
            return ($product->current_stock ?? 0) < 10;
        })
            ->sortBy('current_stock')
            ->take(10)
            ->values()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $product->current_stock ?? 0,
                    'cost_price' => $product->cost_price ?? 0,
                ];
            });

        // 2. Total Stock Value (Quantity * Cost Price)
        $totalStockValue = $products->sum(function ($product) {
            $stock = $product->current_stock ?? 0;
            $cost = $product->cost_price ?? 0;
            return $stock * $cost;
        });

        // 3. Most Stocked Items (value wise)
        $topValueStock = $products->map(function ($product) {
            $stock = $product->current_stock ?? 0;
            $cost = $product->cost_price ?? 0;
            return [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'current_stock' => $stock,
                'cost_price' => $cost,
                'total_value' => $stock * $cost,
            ];
        })
            ->sortByDesc('total_value')
            ->take(10)
            ->values();

        return $this->respondSuccess([
            'critical_stock' => $criticalStock,
            'total_stock_value' => $totalStockValue,
            'top_value_stock' => $topValueStock
        ], 'Stock report retrieved successfully.');
    }
}

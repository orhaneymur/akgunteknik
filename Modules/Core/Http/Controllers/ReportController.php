<?php

namespace Modules\Core\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Modules\Sales\Models\Order;
use Modules\Sales\Models\OrderItem;
use Modules\Inventory\Models\Product;
use Modules\Finance\Models\Expense;

class ReportController extends Controller
{
    /**
     * Get Main Dashboard Stats (Cards)
     */
    public function dashboardStats()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        // 1. Total Sales (This Month)
        // Adjust for multi-tenancy later
        $salesTotal = Order::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('total_amount') ?? 0;

        // 2. Total Expenses (This Month)
        $expensesTotal = Expense::whereBetween('date', [$startOfMonth, $endOfMonth])
            ->sum('amount') ?? 0;

        // 3. Profit (Sales - Expenses - Cost of Goods Sold)
        // Simplified Profit: Sales - Expenses. Ideally needed COGS (Order Items * Cost Price)
        // Let's try to calculate COGS if possible, or just Stick to Cash Flow Profit
        $profit = $salesTotal - $expensesTotal;

        // 4. Low Stock Count
        $lowStockCount = Product::whereColumn('stock_quantity', '<=', 'min_stock_level')->count();

        // 5. Open Orders
        $openOrders = Order::where('status', 'pending')->count();

        // 6. Chart Data (Last 7 Days Sales)
        $last7Days = collect();
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $dailySales = Order::whereDate('date', $date)->sum('total_amount');
            $last7Days->push(['date' => $date, 'total' => $dailySales]);
        }

        return response()->json([
            'sales_month' => $salesTotal,
            'expenses_month' => $expensesTotal,
            'profit_month' => $profit, // Need to implement COGS for real Net Profit
            'low_stock_count' => $lowStockCount,
            'open_orders_count' => $openOrders,
            'daily_sales' => $last7Days
        ]);
    }

    /**
     * Detailed Sales Report
     */
    public function salesReport(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));

        // Group by Date
        $salesByDate = Order::whereBetween('date', [$startDate, $endDate])
            ->selectRaw('date, count(*) as count, sum(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top Selling Products in Range
        $topProducts = OrderItem::whereHas('order', function ($q) use ($startDate, $endDate) {
            $q->whereBetween('date', [$startDate, $endDate]);
        })
            ->selectRaw('product_id, sum(quantity) as total_qty, sum(total_price) as total_revenue')
            ->groupBy('product_id')
            ->with('product:id,name,sku')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return response()->json([
            'sales_by_date' => $salesByDate,
            'top_products' => $topProducts
        ]);
    }

    /**
     * Detailed Stock Report
     */
    public function stockReport()
    {
        // 1. Critical Stock
        $criticalStock = Product::whereColumn('stock_quantity', '<=', 'min_stock_level')
            ->orderBy('stock_quantity')
            ->get();

        // 2. Stock Value (Quantity * Cost Price)
        $totalStockValue = Product::selectRaw('sum(stock_quantity * cost_price) as total_value')->value('total_value');

        // 3. Most Stocked Items (value wise)
        $topValueStock = Product::selectRaw('id, name, stock_quantity, cost_price, (stock_quantity * cost_price) as total_value')
            ->orderByDesc('total_value')
            ->limit(10)
            ->get();

        return response()->json([
            'critical_stock' => $criticalStock,
            'total_stock_value' => $totalStockValue ?? 0,
            'top_value_stock' => $topValueStock
        ]);
    }
}

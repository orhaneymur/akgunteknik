<?php

namespace Modules\Core\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Sales\Models\Order;
use Modules\Customer\Models\Customer;
use Modules\Inventory\Models\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends BaseController
{
    public function index(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Sales Stats
        $sales = Order::with('items.product')->where('tenant_id', $tenantId)->get();
        $totalSales = $sales->sum('total_amount');

        $totalCost = 0;
        foreach ($sales as $sale) {
            foreach ($sale->items as $item) {
                // Calculate cost: Quantity * (Product Cost Price at the moment - usually we should store historical cost in order_items but for now we use current product cost OR order item cost if we had it. 
                // In Phase 2 we added unit_cost to purchase_order_items, but for Sales we might not have linked to specific stock batch cost (FIFO/LIFO). 
                // Simplified approach: Order Item Quantity * Current Product Cost Price.
                if ($item->product) {
                    $totalCost += $item->quantity * $item->product->cost_price;
                }
            }
        }

        $totalProfit = $totalSales - $totalCost;

        // Total Customers
        $totalCustomers = Customer::where('tenant_id', $tenantId)->count();

        // Total Products
        $totalProducts = Product::where('tenant_id', $tenantId)->count();

        // Recent Orders
        $recentOrders = Order::with('customer')
            ->where('tenant_id', $tenantId)
            ->latest()
            ->take(5)
            ->get();

        // Low Stock Products - Optimized with single query
        $lowStockProducts = Product::where('tenant_id', $tenantId)
            ->withSum('inventoryMovements as current_stock', 'quantity')
            ->having('current_stock', '<', 10)
            ->orderBy('current_stock')
            ->limit(5)
            ->get()
            ->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'current_stock' => $product->current_stock ?? 0,
                ];
            });

        // Top Selling - Optimized with single query (no N+1)
        $topSellingData = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.tenant_id', $tenantId)
            ->whereNull('products.deleted_at') // Exclude soft deleted products
            ->select('products.name', DB::raw('SUM(order_items.quantity) as total_qty'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->name,
                    'qty' => $item->total_qty
                ];
            });

        return $this->respondSuccess([
            'total_sales' => $totalSales,
            'total_cost' => $totalCost,
            'total_profit' => $totalProfit,
            'total_customers' => $totalCustomers,
            'total_products' => $totalProducts,
            'recent_orders' => $recentOrders,
            'low_stock_products' => $lowStockProducts,
            'top_selling' => $topSellingData
        ], 'Dashboard stats retrieved successfully.');
    }
}

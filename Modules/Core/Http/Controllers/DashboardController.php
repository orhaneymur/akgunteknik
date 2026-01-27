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

        // Low Stock Products
        $lowStockProducts = [];
        // Optimized: only fetch needed columns and check relationship count if possible, 
        // but here we are iterating. For <100 products this is fine.
        $products = Product::where('tenant_id', $tenantId)->get();

        foreach ($products as $product) {
            // This loop N+1 is bad for large data, but acceptable for MVP with small catalog
            $stock = $product->inventoryMovements()->sum('quantity');
            if ($stock < 10) {
                $product->current_stock = $stock;
                $lowStockProducts[] = $product;
            }
        }
        $lowStockProducts = array_slice($lowStockProducts, 0, 5);

        // Top Selling (Simplified)
        $topSelling = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.tenant_id', $tenantId)
            ->select('product_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('product_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        // Map product names
        $topSellingData = [];
        foreach ($topSelling as $item) {
            $p = Product::find($item->product_id);
            if ($p) {
                $topSellingData[] = [
                    'name' => $p->name,
                    'qty' => $item->total_qty
                ];
            }
        }

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

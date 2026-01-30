<?php

namespace Modules\Inventory\Services;

use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ReservedStock;
use Modules\Sales\Models\Order;
use Illuminate\Support\Facades\DB;

class StockReservationService
{
    /**
     * Reserve stock for an order
     */
    public function reserveStock(Order $order, array $items, int $warehouseId): array
    {
        $reservations = [];
        $errors = [];

        foreach ($items as $item) {
            $product = Product::where('id', $item['product_id'])
                ->where('tenant_id', $order->tenant_id)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                $errors[] = "Product ID {$item['product_id']} not found.";
                continue;
            }

            // Calculate available stock (current stock - reserved stock)
            $currentStock = $product->inventoryMovements()->sum('quantity') ?? 0;
            $reservedStock = ReservedStock::where('product_id', $product->id)
                ->where('warehouse_id', $warehouseId)
                ->where('status', 'reserved')
                ->sum('quantity');

            $availableStock = $currentStock - $reservedStock;
            $requestedQuantity = $item['quantity'];

            if ($availableStock < $requestedQuantity) {
                $errors[] = "Ürün '{$product->name}' için yetersiz stok. Mevcut: {$availableStock}, İstenen: {$requestedQuantity}";
                continue;
            }

            // Create reservation
            $reservation = ReservedStock::create([
                'tenant_id' => $order->tenant_id,
                'product_id' => $product->id,
                'warehouse_id' => $warehouseId,
                'order_id' => $order->id,
                'quantity' => $requestedQuantity,
                'status' => 'reserved',
                'reserved_at' => now(),
            ]);

            $reservations[] = $reservation;
        }

        return ['reservations' => $reservations, 'errors' => $errors];
    }

    /**
     * Release reserved stock (when order is cancelled)
     */
    public function releaseStock(Order $order): void
    {
        ReservedStock::where('order_id', $order->id)
            ->where('status', 'reserved')
            ->update([
                'status' => 'released',
                'released_at' => now(),
            ]);
    }

    /**
     * Fulfill reserved stock (when order is shipped/delivered - convert to actual stock movement)
     */
    public function fulfillStock(Order $order): void
    {
        ReservedStock::where('order_id', $order->id)
            ->where('status', 'reserved')
            ->update([
                'status' => 'fulfilled',
            ]);
    }

    /**
     * Get available stock (current - reserved)
     */
    public function getAvailableStock(int $productId, int $warehouseId): int
    {
        $product = Product::find($productId);
        if (!$product) {
            return 0;
        }

        $currentStock = $product->inventoryMovements()->sum('quantity') ?? 0;
        $reservedStock = ReservedStock::where('product_id', $productId)
            ->where('warehouse_id', $warehouseId)
            ->where('status', 'reserved')
            ->sum('quantity');

        return max(0, $currentStock - $reservedStock);
    }
}

<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Sales\Models\Order;
use Modules\Sales\Models\OrderStatusHistory;
use Modules\Customer\Models\Customer;
use Modules\Inventory\Models\Product;
use Modules\Inventory\Models\ReservedStock;
use App\Models\User;

class OrderStatusTest extends TestCase
{
    public function test_user_can_update_order_status()
    {
        $user = $this->createTestUser('staff');
        $token = $this->getAuthToken($user);

        $customer = Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Customer',
            'customer_type' => 'b2b',
        ]);

        $product = Product::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'base_price' => 100.00,
        ]);

        $order = Order::create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-20260130-0001',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        // Update status to processing
        $response = $this->postJson("/api/sales/orders/{$order->id}/status", [
            'status' => 'processing',
            'notes' => 'Order is being processed',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $order->refresh();
        $this->assertEquals('processing', $order->status);
        $this->assertNotNull($order->processing_at);

        // Verify status history was recorded
        $history = OrderStatusHistory::where('order_id', $order->id)
            ->where('status', 'processing')
            ->first();
        $this->assertNotNull($history);
    }

    public function test_order_status_update_to_shipped_deducts_stock()
    {
        $user = $this->createTestUser('staff');
        $token = $this->getAuthToken($user);

        $customer = Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Customer',
            'customer_type' => 'b2b',
        ]);

        $product = Product::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'base_price' => 100.00,
        ]);

        // Add stock
        \Modules\Inventory\Models\InventoryMovement::create([
            'tenant_id' => $user->tenant_id,
            'product_id' => $product->id,
            'warehouse_id' => $user->warehouse_id,
            'quantity' => 10,
            'type' => 'purchase',
        ]);

        $order = Order::create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-20260130-0002',
            'total_amount' => 100.00,
            'status' => 'pending',
        ]);

        // Create order item
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 5,
            'unit_price' => 100.00,
            'total_price' => 500.00,
        ]);

        // Reserve stock
        ReservedStock::create([
            'tenant_id' => $user->tenant_id,
            'product_id' => $product->id,
            'warehouse_id' => $user->warehouse_id,
            'order_id' => $order->id,
            'quantity' => 5,
            'status' => 'reserved',
            'reserved_at' => now(),
        ]);

        // Update status to shipped
        $response = $this->postJson("/api/sales/orders/{$order->id}/status", [
            'status' => 'shipped',
            'carrier' => 'Test Carrier',
            'tracking_number' => 'TRACK123',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $this->assertEquals('shipped', $order->status);
        $this->assertNotNull($order->shipped_at);
        $this->assertEquals('Test Carrier', $order->carrier);
        $this->assertEquals('TRACK123', $order->tracking_number);

        // Verify stock was deducted
        $stockMovement = \Modules\Inventory\Models\InventoryMovement::where('product_id', $product->id)
            ->where('type', 'sale')
            ->first();
        $this->assertNotNull($stockMovement);
        $this->assertEquals(-5, $stockMovement->quantity);
    }
}

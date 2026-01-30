<?php

namespace Tests\Feature;

use Tests\TestCase;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\Invoice;
use Modules\Sales\Models\Order;
use Modules\Customer\Models\Customer;
use Modules\Core\Models\TaxRate;
use Modules\Inventory\Models\Product;
use App\Models\User;

class PaymentTest extends TestCase
{
    public function test_user_can_record_payment_for_invoice()
    {
        $user = $this->createTestUser('manager');
        $token = $this->getAuthToken($user);

        // Create customer
        $customer = Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Customer',
            'customer_type' => 'b2b',
            'current_balance' => 1000.00,
        ]);

        // Create tax rate
        $taxRate = TaxRate::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'KDV %20',
            'rate' => 20.00,
            'is_active' => true,
        ]);

        // Create product
        $product = Product::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Product',
            'sku' => 'TEST-001',
            'base_price' => 100.00,
            'tax_rate_id' => $taxRate->id,
        ]);

        // Create order
        $order = Order::create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $customer->id,
            'order_number' => 'ORD-20260130-0001',
            'total_amount' => 120.00,
            'paid_amount' => 0,
            'remaining_amount' => 120.00,
            'status' => 'pending',
        ]);

        // Create invoice from order
        $invoice = Invoice::create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $customer->id,
            'invoiceable_type' => Order::class,
            'invoiceable_id' => $order->id,
            'invoice_number' => 'FAT-20260130-0001',
            'invoice_series' => 'FAT',
            'invoice_number_sequence' => 1,
            'contact_name' => 'Test Customer',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'total_amount' => 120.00,
            'subtotal_amount' => 100.00,
            'tax_amount' => 20.00,
            'paid_amount' => 0,
            'remaining_amount' => 120.00,
            'status' => 'draft',
        ]);

        // Record payment
        $response = $this->postJson('/api/finance/payments', [
            'payable_type' => 'invoice',
            'payable_id' => $invoice->id,
            'amount' => 60.00,
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'amount',
                    'payment_method',
                    'payable_type',
                    'payable_id',
                ],
            ])
            ->assertJson(['success' => true]);

        // Verify invoice was updated
        $invoice->refresh();
        $this->assertEquals(60.00, $invoice->paid_amount);
        $this->assertEquals(60.00, $invoice->remaining_amount);

        // Verify customer balance was updated
        $customer->refresh();
        $this->assertEquals(940.00, $customer->current_balance); // 1000 - 60
    }

    public function test_payment_amount_cannot_exceed_remaining_amount()
    {
        $user = $this->createTestUser('manager');
        $token = $this->getAuthToken($user);

        $customer = Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Customer',
            'customer_type' => 'b2b',
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $customer->id,
            'invoice_number' => 'FAT-20260130-0002',
            'invoice_series' => 'FAT',
            'invoice_number_sequence' => 2,
            'contact_name' => 'Test Customer',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'total_amount' => 100.00,
            'paid_amount' => 0,
            'remaining_amount' => 100.00,
            'status' => 'draft',
        ]);

        $response = $this->postJson('/api/finance/payments', [
            'payable_type' => 'invoice',
            'payable_id' => $invoice->id,
            'amount' => 150.00, // Exceeds remaining amount
            'payment_date' => now()->toDateString(),
            'payment_method' => 'cash',
        ], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(422)
            ->assertJson(['success' => false]);
    }

    public function test_user_can_cancel_payment()
    {
        $user = $this->createTestUser('manager');
        $token = $this->getAuthToken($user);

        $customer = Customer::create([
            'tenant_id' => $user->tenant_id,
            'name' => 'Test Customer',
            'customer_type' => 'b2b',
            'current_balance' => 1000.00,
        ]);

        $invoice = Invoice::create([
            'tenant_id' => $user->tenant_id,
            'customer_id' => $customer->id,
            'invoice_number' => 'FAT-20260130-0003',
            'invoice_series' => 'FAT',
            'invoice_number_sequence' => 3,
            'contact_name' => 'Test Customer',
            'issue_date' => now(),
            'due_date' => now()->addDays(14),
            'total_amount' => 100.00,
            'paid_amount' => 50.00,
            'remaining_amount' => 50.00,
            'status' => 'draft',
        ]);

        $payment = Payment::create([
            'tenant_id' => $user->tenant_id,
            'payable_type' => Invoice::class,
            'payable_id' => $invoice->id,
            'customer_id' => $customer->id,
            'amount' => 50.00,
            'payment_date' => now(),
            'payment_method' => 'cash',
            'status' => 'completed',
        ]);

        $response = $this->postJson("/api/finance/payments/{$payment->id}/cancel", [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Verify payment was cancelled
        $payment->refresh();
        $this->assertEquals('cancelled', $payment->status);

        // Verify invoice was updated
        $invoice->refresh();
        $this->assertEquals(0.00, $invoice->paid_amount);
        $this->assertEquals(100.00, $invoice->remaining_amount);
    }
}

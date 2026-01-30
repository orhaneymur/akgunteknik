<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\Payment;
use Modules\Finance\Models\Invoice;
use Modules\Sales\Models\Order;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Record a payment for an invoice or order
     */
    public function recordPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $payable = $this->getPayable($data['payable_type'], $data['payable_id']);

            // Create payment
            $payment = Payment::create([
                'tenant_id' => $data['tenant_id'],
                'payable_type' => $data['payable_type'],
                'payable_id' => $data['payable_id'],
                'customer_id' => $data['customer_id'] ?? $payable->customer_id,
                'amount' => $data['amount'],
                'payment_date' => $data['payment_date'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'notes' => $data['notes'] ?? null,
                'safe_id' => $data['safe_id'] ?? null,
                'status' => $data['status'] ?? 'completed',
                'recorded_by' => $data['recorded_by'] ?? null,
            ]);

            // Update payable (invoice or order)
            $this->updatePayablePayment($payable, $data['amount']);

            // Update customer balance
            if ($payment->customer_id) {
                $this->updateCustomerBalance($payment->customer_id, $data['amount'], 'payment');
            }

            // Update safe balance if safe is specified
            if ($payment->safe_id) {
                $this->updateSafeBalance($payment->safe_id, $data['amount']);
            }

            Log::info('Payment recorded', [
                'payment_id' => $payment->id,
                'payable_type' => $data['payable_type'],
                'payable_id' => $data['payable_id'],
                'amount' => $data['amount'],
            ]);

            return $payment;
        });
    }

    /**
     * Cancel a payment
     */
    public function cancelPayment(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payable = $this->getPayable($payment->payable_type, $payment->payable_id);

            // Reverse payment amount
            $this->updatePayablePayment($payable, -$payment->amount);

            // Reverse customer balance
            if ($payment->customer_id) {
                $this->updateCustomerBalance($payment->customer_id, -$payment->amount, 'payment_cancelled');
            }

            // Reverse safe balance
            if ($payment->safe_id) {
                $this->updateSafeBalance($payment->safe_id, -$payment->amount);
            }

            $payment->update(['status' => 'cancelled']);

            Log::info('Payment cancelled', [
                'payment_id' => $payment->id,
            ]);
        });
    }

    /**
     * Get payable model (Invoice or Order)
     */
    protected function getPayable(string $type, int $id)
    {
        if ($type === Invoice::class || $type === 'invoice') {
            return Invoice::findOrFail($id);
        } elseif ($type === Order::class || $type === 'order') {
            return Order::findOrFail($id);
        }

        throw new \InvalidArgumentException("Invalid payable type: {$type}");
    }

    /**
     * Update payable payment amounts
     */
    protected function updatePayablePayment($payable, float $amount): void
    {
        $payable->increment('paid_amount', $amount);
        $payable->decrement('remaining_amount', $amount);

        // If fully paid, set paid_at
        if ($payable->remaining_amount <= 0 && !$payable->paid_at) {
            $payable->update(['paid_at' => now()]);
        } elseif ($payable->remaining_amount > 0 && $payable->paid_at) {
            $payable->update(['paid_at' => null]);
        }
    }

    /**
     * Update customer balance
     */
    protected function updateCustomerBalance(int $customerId, float $amount, string $type = 'payment'): void
    {
        $customer = Customer::lockForUpdate()->findOrFail($customerId);

        // Payment reduces customer debt (decreases balance)
        // Balance = Total Sales - Total Payments
        // So when payment is made, balance decreases
        $customer->decrement('current_balance', $amount);
    }

    /**
     * Update safe balance
     */
    protected function updateSafeBalance(int $safeId, float $amount): void
    {
        $safe = \Modules\Finance\Models\Safe::lockForUpdate()->findOrFail($safeId);
        $safe->increment('balance', $amount);
    }
}

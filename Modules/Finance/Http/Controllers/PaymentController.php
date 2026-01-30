<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Finance\Models\Payment;
use Modules\Finance\Models\Invoice;
use Modules\Sales\Models\Order;
use Modules\Finance\Services\PaymentService;
use Illuminate\Support\Facades\Validator;

class PaymentController extends BaseController
{
    protected $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $query = Payment::with(['customer', 'safe', 'payable', 'recordedBy'])
            ->where('tenant_id', $request->user()->tenant_id);

        // Filter by customer
        if ($request->has('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Filter by payment method
        if ($request->has('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('payment_date', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('payment_date', '<=', $request->end_date);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $payments = $query->latest('payment_date')->paginate($request->input('per_page', 15));

        return $this->respondSuccess($payments, 'Payments retrieved successfully.');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payable_type' => 'required|in:invoice,order',
            'payable_id' => 'required|integer',
            'customer_id' => 'nullable|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|in:cash,credit_card,bank_transfer,check,other',
            'reference_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'safe_id' => 'nullable|exists:safes,id',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            // Get payable and validate tenant ownership
            $payableType = $request->payable_type === 'invoice' ? Invoice::class : Order::class;
            $payable = $payableType::where('id', $request->payable_id)
                ->where('tenant_id', $request->user()->tenant_id)
                ->first();

            if (!$payable) {
                return $this->respondError([], 'Invoice or Order not found.', 404);
            }

            // Check if payment amount exceeds remaining amount
            if ($request->amount > $payable->remaining_amount) {
                return $this->respondError(
                    ['amount' => 'Payment amount cannot exceed remaining amount.'],
                    'Invalid payment amount.',
                    422
                );
            }

            // Record payment
            $payment = $this->paymentService->recordPayment([
                'tenant_id' => $request->user()->tenant_id,
                'payable_type' => $payableType,
                'payable_id' => $request->payable_id,
                'customer_id' => $request->customer_id ?? $payable->customer_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes' => $request->notes,
                'safe_id' => $request->safe_id,
                'recorded_by' => $request->user()->id,
            ]);

            return $this->respondSuccess($payment->load(['customer', 'safe', 'payable']), 'Payment recorded successfully.', 201);

        } catch (\Exception $e) {
            Log::error('Payment creation failed', [
                'error' => $e->getMessage(),
                'tenant_id' => $request->user()->tenant_id,
                'user_id' => $request->user()->id,
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to record payment.', 500);
        }
    }

    public function show(Request $request, $id)
    {
        $payment = Payment::with(['customer', 'safe', 'payable', 'recordedBy'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$payment) {
            return $this->respondError([], 'Payment not found.', 404);
        }

        return $this->respondSuccess($payment, 'Payment retrieved successfully.');
    }

    public function cancel(Request $request, $id)
    {
        $payment = Payment::where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('status', 'completed')
            ->first();

        if (!$payment) {
            return $this->respondError([], 'Payment not found or already cancelled.', 404);
        }

        try {
            $this->paymentService->cancelPayment($payment);

            return $this->respondSuccess($payment->fresh(), 'Payment cancelled successfully.');

        } catch (\Exception $e) {
            Log::error('Payment cancellation failed', [
                'payment_id' => $id,
                'error' => $e->getMessage(),
            ]);
            return $this->respondError(['error' => $e->getMessage()], 'Failed to cancel payment.', 500);
        }
    }
}

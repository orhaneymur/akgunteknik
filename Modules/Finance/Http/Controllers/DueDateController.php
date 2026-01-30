<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Finance\Models\Invoice;
use Modules\Sales\Models\Order;
use Illuminate\Support\Facades\DB;

class DueDateController extends BaseController
{
    /**
     * Get overdue invoices
     */
    public function overdueInvoices(Request $request)
    {
        $invoices = Invoice::with(['customer', 'items'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('remaining_amount', '>', 0)
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->get();

        // Add days overdue to each invoice
        $invoices->each(function ($invoice) {
            $invoice->days_overdue = now()->diffInDays($invoice->due_date);
        });

        return $this->respondSuccess($invoices, 'Overdue invoices retrieved successfully.');
    }

    /**
     * Get invoices due soon (within X days)
     */
    public function dueSoonInvoices(Request $request)
    {
        $days = $request->input('days', 7);
        $dueDate = now()->addDays($days);

        $invoices = Invoice::with(['customer', 'items'])
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('remaining_amount', '>', 0)
            ->whereBetween('due_date', [now(), $dueDate])
            ->orderBy('due_date', 'asc')
            ->get();

        // Add days until due to each invoice
        $invoices->each(function ($invoice) {
            $invoice->days_until_due = now()->diffInDays($invoice->due_date, false);
        });

        return $this->respondSuccess($invoices, 'Due soon invoices retrieved successfully.');
    }

    /**
     * Get customer payment summary
     */
    public function customerPaymentSummary(Request $request, $customerId)
    {
        $customer = \Modules\Customer\Models\Customer::where('id', $customerId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$customer) {
            return $this->respondError([], 'Customer not found.', 404);
        }

        // Get all unpaid invoices
        $unpaidInvoices = Invoice::where('customer_id', $customerId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('remaining_amount', '>', 0)
            ->orderBy('due_date', 'asc')
            ->get();

        // Get all unpaid orders
        $unpaidOrders = Order::where('customer_id', $customerId)
            ->where('tenant_id', $request->user()->tenant_id)
            ->where('remaining_amount', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        // Calculate totals
        $totalDebt = $unpaidInvoices->sum('remaining_amount') + $unpaidOrders->sum('remaining_amount');
        $overdueAmount = $unpaidInvoices->where('due_date', '<', now())->sum('remaining_amount');

        return $this->respondSuccess([
            'customer' => $customer,
            'current_balance' => $customer->current_balance,
            'total_debt' => $totalDebt,
            'overdue_amount' => $overdueAmount,
            'unpaid_invoices' => $unpaidInvoices,
            'unpaid_orders' => $unpaidOrders,
        ], 'Customer payment summary retrieved successfully.');
    }

    /**
     * Get due date report
     */
    public function dueDateReport(Request $request)
    {
        $tenantId = $request->user()->tenant_id;

        // Overdue invoices
        $overdueInvoices = Invoice::where('tenant_id', $tenantId)
            ->where('remaining_amount', '>', 0)
            ->where('due_date', '<', now())
            ->get();

        // Due this week
        $dueThisWeek = Invoice::where('tenant_id', $tenantId)
            ->where('remaining_amount', '>', 0)
            ->whereBetween('due_date', [now(), now()->addWeek()])
            ->get();

        // Due this month
        $dueThisMonth = Invoice::where('tenant_id', $tenantId)
            ->where('remaining_amount', '>', 0)
            ->whereBetween('due_date', [now(), now()->addMonth()])
            ->get();

        // Totals
        $overdueTotal = $overdueInvoices->sum('remaining_amount');
        $dueThisWeekTotal = $dueThisWeek->sum('remaining_amount');
        $dueThisMonthTotal = $dueThisMonth->sum('remaining_amount');

        return $this->respondSuccess([
            'overdue' => [
                'count' => $overdueInvoices->count(),
                'total_amount' => $overdueTotal,
                'invoices' => $overdueInvoices,
            ],
            'due_this_week' => [
                'count' => $dueThisWeek->count(),
                'total_amount' => $dueThisWeekTotal,
                'invoices' => $dueThisWeek,
            ],
            'due_this_month' => [
                'count' => $dueThisMonth->count(),
                'total_amount' => $dueThisMonthTotal,
                'invoices' => $dueThisMonth,
            ],
        ], 'Due date report retrieved successfully.');
    }
}

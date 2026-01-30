<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;
use Modules\Finance\Services\InvoicePdfService;
use Modules\Sales\Models\Order;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Customer\Models\Customer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Response;

class InvoiceController extends BaseController
{
    public function index(Request $request)
    {
        $invoices = Invoice::with('customer')
            ->where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(15);

        return $this->respondSuccess($invoices, 'Invoices retrieved successfully.');
    }

    public function show(Request $request, $id)
    {
        $invoice = Invoice::with(['items', 'customer', 'invoiceable', 'tenant'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$invoice) {
            return $this->respondError([], 'Invoice not found.', 404);
        }

        return $this->respondSuccess($invoice, 'Invoice retrieved successfully.');
    }

    public function downloadPdf(Request $request, $id)
    {
        $invoice = Invoice::with(['items', 'customer', 'tenant'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$invoice) {
            return $this->respondError([], 'Invoice not found.', 404);
        }

        $pdfService = new InvoicePdfService();
        $pdfContent = $pdfService->generate($invoice);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="fatura-' . $invoice->invoice_number . '.pdf"',
        ]);
    }

    public function viewPdf(Request $request, $id)
    {
        $invoice = Invoice::with(['items', 'customer', 'tenant'])
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$invoice) {
            return $this->respondError([], 'Invoice not found.', 404);
        }

        $pdfService = new InvoicePdfService();
        $pdfContent = $pdfService->generate($invoice);

        return response($pdfContent, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="fatura-' . $invoice->invoice_number . '.pdf"',
        ]);
    }

    /**
     * Create an invoice from a source (Order or PurchaseOrder)
     */
    public function storeFromSource(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_type' => 'required|in:order,purchase_order',
            'source_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->respondError($validator->errors(), 'Validation Error', 422);
        }

        try {
            DB::beginTransaction();

            $tenantId = $request->user()->tenant_id;
            $source = null;
            $itemsData = [];
            $contactName = '';
            $totalAmount = 0;
            $invoiceType = '';

            if ($request->source_type === 'order') {
                $source = Order::with(['items.product.taxRate', 'customer'])
                    ->where('id', $request->source_id)
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                $customer = $source->customer;
                $contactName = $customer ? $customer->name : 'Unknown Customer';
                $customerType = $customer ? $customer->customer_type : 'b2b';
                $invoiceType = Order::class;

                $subtotalAmount = 0;
                $totalTaxAmount = 0;

                foreach ($source->items as $item) {
                    $product = $item->product;
                    $productName = $product ? $product->name : 'Unknown Product (ID: ' . $item->product_id . ')';
                    
                    // Get tax rate from product (default to 20% if not set)
                    $taxRate = $product && $product->taxRate ? $product->taxRate->rate : 20.00;
                    
                    // Calculate line totals (KDV hariç fiyatlandırma)
                    $lineSubtotal = $item->quantity * $item->unit_price;
                    $lineTaxAmount = $lineSubtotal * ($taxRate / 100);
                    $lineTotal = $lineSubtotal + $lineTaxAmount;

                    $subtotalAmount += $lineSubtotal;
                    $totalTaxAmount += $lineTaxAmount;

                    $itemsData[] = [
                        'description' => $productName,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'tax_rate' => $taxRate,
                        'total' => $lineTotal,
                    ];
                }

                $totalAmount = $subtotalAmount + $totalTaxAmount;

            } elseif ($request->source_type === 'purchase_order') {
                $source = PurchaseOrder::with(['items.product.taxRate', 'supplier'])
                    ->where('id', $request->source_id)
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                $contactName = $source->supplier ? $source->supplier->name : 'Unknown Supplier';
                $invoiceType = PurchaseOrder::class;

                $subtotalAmount = 0;
                $totalTaxAmount = 0;

                foreach ($source->items as $item) {
                    $product = $item->product;
                    $productName = $product ? $product->name : 'Unknown Product (ID: ' . $item->product_id . ')';
                    
                    // Get tax rate from product (default to 20% if not set)
                    $taxRate = $product && $product->taxRate ? $product->taxRate->rate : 20.00;
                    
                    // Calculate line totals (KDV hariç fiyatlandırma)
                    $lineSubtotal = $item->quantity * $item->unit_cost;
                    $lineTaxAmount = $lineSubtotal * ($taxRate / 100);
                    $lineTotal = $lineSubtotal + $lineTaxAmount;

                    $subtotalAmount += $lineSubtotal;
                    $totalTaxAmount += $lineTaxAmount;

                    $itemsData[] = [
                        'description' => $productName,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_cost,
                        'tax_rate' => $taxRate,
                        'total' => $lineTotal,
                    ];
                }

                $totalAmount = $subtotalAmount + $totalTaxAmount;
            }

            // Check if invoice already exists for this source
            $exists = Invoice::where('invoiceable_type', $invoiceType)
                ->where('invoiceable_id', $source->id)
                ->first();

            if ($exists) {
                return $this->respondError(['invoice_id' => $exists->id], 'Invoice already exists for this order.', 409);
            }

            // Determine invoice series based on customer type (for sales) or default
            if ($request->source_type === 'order' && isset($customerType)) {
                $invoiceSeries = $customerType === 'b2c' ? 'PER' : 'FAT'; // PER = Perakende, FAT = Ticari Fatura
            } else {
                $invoiceSeries = 'FAT'; // Default for purchase orders
            }

            // Generate sequential invoice number
            $datePrefix = date('Ymd');
            $lastInvoice = Invoice::where('tenant_id', $tenantId)
                ->where('invoice_series', $invoiceSeries)
                ->whereDate('created_at', today())
                ->orderBy('invoice_number_sequence', 'desc')
                ->first();

            $sequence = $lastInvoice ? $lastInvoice->invoice_number_sequence + 1 : 1;
            $invoiceNumber = $invoiceSeries . '-' . $datePrefix . '-' . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'customer_id' => ($request->source_type === 'order' && isset($customer)) ? $customer->id : null,
                'invoiceable_type' => $invoiceType,
                'invoiceable_id' => $source->id,
                'invoice_number' => $invoiceNumber,
                'invoice_series' => $invoiceSeries,
                'invoice_number_sequence' => $sequence,
                'contact_name' => $contactName,
                'issue_date' => now(),
                'due_date' => now()->addDays(14),
                'subtotal_amount' => $subtotalAmount,
                'tax_amount' => $totalTaxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => 0,
                'remaining_amount' => $totalAmount,
                'status' => 'draft',
            ]);

            // Update customer balance (increase debt)
            if ($invoice->customer_id) {
                $customer = Customer::lockForUpdate()->find($invoice->customer_id);
                if ($customer) {
                    $customer->increment('current_balance', $totalAmount);
                }
            }

            $invoice->items()->createMany($itemsData);

            DB::commit();

            return $this->respondSuccess($invoice, 'Invoice created successfully.', 201);

        } catch (\Exception $e) {
            DB::rollBack();
            \Illuminate\Support\Facades\Log::error('Invoice Creation Failed: ' . $e->getMessage());
            \Illuminate\Support\Facades\Log::error($e->getTraceAsString());
            return $this->respondError(['error' => $e->getMessage()], 'Failed to create invoice.', 500);
        }
    }
}

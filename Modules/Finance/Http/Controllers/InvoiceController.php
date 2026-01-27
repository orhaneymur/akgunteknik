<?php

namespace Modules\Finance\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Finance\Models\Invoice;
use Modules\Finance\Models\InvoiceItem;
use Modules\Sales\Models\Order;
use Modules\Inventory\Models\PurchaseOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class InvoiceController extends BaseController
{
    public function index(Request $request)
    {
        $invoices = Invoice::where('tenant_id', $request->user()->tenant_id)
            ->latest()
            ->paginate(15);

        return $this->respondSuccess($invoices, 'Invoices retrieved successfully.');
    }

    public function show(Request $request, $id)
    {
        $invoice = Invoice::with('items')
            ->where('id', $id)
            ->where('tenant_id', $request->user()->tenant_id)
            ->first();

        if (!$invoice) {
            return $this->respondError([], 'Invoice not found.', 404);
        }

        return $this->respondSuccess($invoice, 'Invoice retrieved successfully.');
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
                $source = Order::with(['items.product', 'customer'])
                    ->where('id', $request->source_id)
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                $contactName = $source->customer ? $source->customer->name : 'Unknown Customer';
                $totalAmount = $source->total_amount;
                $invoiceType = Order::class;

                foreach ($source->items as $item) {
                    $productName = $item->product ? $item->product->name : 'Unknown Product (ID: ' . $item->product_id . ')';
                    $itemsData[] = [
                        'description' => $productName,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total' => $item->total_price,
                        'tax_rate' => 0, // Assume 0 or derived in future
                    ];
                }

            } elseif ($request->source_type === 'purchase_order') {
                $source = PurchaseOrder::with(['items.product', 'supplier'])
                    ->where('id', $request->source_id)
                    ->where('tenant_id', $tenantId)
                    ->firstOrFail();

                $contactName = $source->supplier ? $source->supplier->name : 'Unknown Supplier';
                $totalAmount = $source->total_amount;
                $invoiceType = PurchaseOrder::class;

                foreach ($source->items as $item) {
                    $productName = $item->product ? $item->product->name : 'Unknown Product (ID: ' . $item->product_id . ')';
                    $itemsData[] = [
                        'description' => $productName,
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_cost,
                        'total' => $item->total_cost,
                        'tax_rate' => 0,
                    ];
                }
            }

            // Check if invoice already exists for this source
            $exists = Invoice::where('invoiceable_type', $invoiceType)
                ->where('invoiceable_id', $source->id)
                ->first();

            if ($exists) {
                return $this->respondError(['invoice_id' => $exists->id], 'Invoice already exists for this order.', 409);
            }

            // Generate Invoice Number
            $prefix = $request->source_type === 'order' ? 'INV-SAL-' : 'INV-PUR-';
            $number = $prefix . date('Ymd') . '-' . Str::upper(Str::random(4));

            $invoice = Invoice::create([
                'tenant_id' => $tenantId,
                'invoiceable_type' => $invoiceType,
                'invoiceable_id' => $source->id,
                'invoice_number' => $number,
                'contact_name' => $contactName,
                'issue_date' => now(),
                'due_date' => now()->addDays(14),
                'total_amount' => $totalAmount,
                'status' => 'draft',
            ]);

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

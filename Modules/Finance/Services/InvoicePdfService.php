<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\Invoice;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfService
{
    public function generate(Invoice $invoice): string
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);

        $html = $this->generateHtml($invoice);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function generateHtml(Invoice $invoice): string
    {
        $tenant = $invoice->tenant;
        $customer = $invoice->customer;
        $items = $invoice->items;

        // Format currency
        $formatCurrency = function ($amount) {
            return number_format($amount, 2, ',', '.') . ' ₺';
        };

        // Format date
        $formatDate = function ($date) {
            return $date ? $date->format('d.m.Y') : '-';
        };

        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            margin-bottom: 30px;
        }
        .company-info {
            float: left;
            width: 50%;
        }
        .invoice-info {
            float: right;
            width: 45%;
            text-align: right;
        }
        .clear {
            clear: both;
        }
        .customer-info {
            margin: 20px 0;
            padding: 15px;
            background-color: #f5f5f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #4a5568;
            color: white;
            font-weight: bold;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals {
            margin-top: 20px;
            float: right;
            width: 300px;
        }
        .total-row {
            padding: 5px 0;
        }
        .total-label {
            display: inline-block;
            width: 200px;
            text-align: right;
            padding-right: 10px;
        }
        .total-value {
            display: inline-block;
            width: 100px;
            text-align: right;
            font-weight: bold;
        }
        .grand-total {
            font-size: 16px;
            font-weight: bold;
            border-top: 2px solid #333;
            padding-top: 10px;
            margin-top: 10px;
        }
        .footer {
            margin-top: 50px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2>' . htmlspecialchars($tenant->company_name ?? 'Firma Adı') . '</h2>
            <p>' . htmlspecialchars($tenant->address ?? '') . '</p>
            <p>Vergi Dairesi: ' . htmlspecialchars($tenant->tax_office ?? '') . '</p>
            <p>Vergi No: ' . htmlspecialchars($tenant->tax_number ?? '') . '</p>
            <p>Tel: ' . htmlspecialchars($tenant->phone ?? '') . '</p>
        </div>
        <div class="invoice-info">
            <h1>FATURA</h1>
            <p><strong>Fatura No:</strong> ' . htmlspecialchars($invoice->invoice_number) . '</p>
            <p><strong>Seri:</strong> ' . htmlspecialchars($invoice->invoice_series ?? 'FAT') . '</p>
            <p><strong>Tarih:</strong> ' . $formatDate($invoice->issue_date) . '</p>
            <p><strong>Vade:</strong> ' . $formatDate($invoice->due_date) . '</p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="customer-info">
        <h3>Müşteri Bilgileri</h3>
        <p><strong>Adı:</strong> ' . htmlspecialchars($invoice->contact_name) . '</p>';

        if ($customer) {
            $html .= '<p><strong>Adres:</strong> ' . htmlspecialchars($customer->address ?? '') . '</p>';
            if ($customer->tax_number) {
                $html .= '<p><strong>Vergi No:</strong> ' . htmlspecialchars($customer->tax_number) . '</p>';
                $html .= '<p><strong>Vergi Dairesi:</strong> ' . htmlspecialchars($customer->tax_office ?? '') . '</p>';
            }
            if ($customer->phone) {
                $html .= '<p><strong>Tel:</strong> ' . htmlspecialchars($customer->phone) . '</p>';
            }
        }

        $html .= '</div>

    <table>
        <thead>
            <tr>
                <th>Sıra</th>
                <th>Açıklama</th>
                <th class="text-center">Miktar</th>
                <th class="text-right">Birim Fiyat</th>
                <th class="text-right">KDV %</th>
                <th class="text-right">Toplam</th>
            </tr>
        </thead>
        <tbody>';

        $index = 1;
        foreach ($items as $item) {
            $lineSubtotal = $item->quantity * $item->unit_price;
            $lineTax = $lineSubtotal * ($item->tax_rate / 100);
            $lineTotal = $lineSubtotal + $lineTax;

            $html .= '<tr>
                <td>' . $index++ . '</td>
                <td>' . htmlspecialchars($item->description) . '</td>
                <td class="text-center">' . number_format($item->quantity, 2, ',', '.') . '</td>
                <td class="text-right">' . $formatCurrency($item->unit_price) . '</td>
                <td class="text-right">' . number_format($item->tax_rate, 2, ',', '.') . '</td>
                <td class="text-right">' . $formatCurrency($lineTotal) . '</td>
            </tr>';
        }

        $html .= '</tbody>
    </table>

    <div class="totals">
        <div class="total-row">
            <span class="total-label">Ara Toplam:</span>
            <span class="total-value">' . $formatCurrency($invoice->subtotal_amount) . '</span>
        </div>
        <div class="total-row">
            <span class="total-label">KDV Toplamı:</span>
            <span class="total-value">' . $formatCurrency($invoice->tax_amount) . '</span>
        </div>
        <div class="total-row grand-total">
            <span class="total-label">Genel Toplam:</span>
            <span class="total-value">' . $formatCurrency($invoice->total_amount) . '</span>
        </div>
    </div>

    <div class="clear"></div>';

        if ($invoice->notes) {
            $html .= '<div class="footer">
            <p><strong>Notlar:</strong> ' . nl2br(htmlspecialchars($invoice->notes)) . '</p>
        </div>';
        }

        $html .= '</body>
</html>';

        return $html;
    }
}

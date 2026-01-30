<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'invoiceable_type',
        'invoiceable_id',
        'invoice_number',
        'invoice_series',
        'invoice_number_sequence',
        'contact_name',
        'issue_date',
        'due_date',
        'total_amount',
        'tax_amount',
        'subtotal_amount',
        'paid_amount',
        'remaining_amount',
        'paid_at',
        'status',
        'notes',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'date',
        'total_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function invoiceable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }

    public function tenant()
    {
        return $this->belongsTo(\Modules\Core\Models\Tenant::class);
    }

    public function payments()
    {
        return $this->morphMany(Payment::class, 'payable');
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->remaining_amount > 0 && $this->due_date && $this->due_date->isPast();
    }

    /**
     * Get days until due date (negative if overdue)
     */
    public function getDaysUntilDue(): ?int
    {
        if (!$this->due_date) {
            return null;
        }

        return now()->diffInDays($this->due_date, false);
    }
}

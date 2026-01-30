<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'payable_type',
        'payable_id',
        'customer_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'safe_id',
        'status',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function payable()
    {
        return $this->morphTo();
    }

    public function customer()
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }

    public function safe()
    {
        return $this->belongsTo(Safe::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by');
    }
}

<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Customer\Models\Customer;

class Quote extends Model
{
    protected $fillable = [
        'tenant_id',
        'customer_id',
        'quote_number',
        'total_amount',
        'status',
        'valid_until',
    ];

    protected $casts = [
        'valid_until' => 'date',
    ];

    public function items()
    {
        return $this->hasMany(QuoteItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}

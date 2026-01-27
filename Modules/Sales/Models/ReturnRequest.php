<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Customer\Models\Customer;
use Modules\Inventory\Models\Supplier;

class ReturnRequest extends Model
{
    // Table name is 'returns' but class name 'Return' is reserved word in PHP 7/8 in some contexts or just bad practice
    protected $table = 'returns';

    protected $fillable = [
        'tenant_id',
        'type',
        'status',
        'order_id',
        'purchase_order_id',
        'customer_id',
        'supplier_id',
        'total_amount',
        'notes',
        'date'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(ReturnItem::class, 'return_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}

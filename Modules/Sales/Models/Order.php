<?php

namespace Modules\Sales\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'customer_id',
        'order_number',
        'total_amount',
        'paid_amount',
        'remaining_amount',
        'paid_at',
        'status',
        'shipping_address',
        'carrier',
        'tracking_number',
        'completed_at',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'paid_at' => 'date',
        'completed_at' => 'datetime',
        'processing_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function customer()
    {
        return $this->belongsTo(\Modules\Customer\Models\Customer::class);
    }

    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function reservedStock()
    {
        return $this->hasMany(\Modules\Inventory\Models\ReservedStock::class);
    }

    public function payments()
    {
        return $this->morphMany(\Modules\Finance\Models\Payment::class, 'payable');
    }
}

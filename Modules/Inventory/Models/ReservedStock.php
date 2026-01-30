<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ReservedStock extends Model
{
    use HasFactory;

    protected $table = 'reserved_stock';

    protected $fillable = [
        'tenant_id',
        'product_id',
        'warehouse_id',
        'order_id',
        'quantity',
        'status',
        'reserved_at',
        'released_at',
        'notes',
    ];

    protected $casts = [
        'reserved_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(\Modules\Core\Models\Warehouse::class);
    }

    public function order()
    {
        return $this->belongsTo(\Modules\Sales\Models\Order::class);
    }
}

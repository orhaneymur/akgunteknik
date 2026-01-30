<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Models\Warehouse;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'supplier_id',
        'warehouse_id',
        'status',
        'total_amount',
        'currency',
        'exchange_rate',
        'total_amount_tl',
        'received_at',
    ];

    protected $casts = [
        'received_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'total_amount_tl' => 'decimal:2',
        'exchange_rate' => 'decimal:4',
    ];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function importCosts()
    {
        return $this->hasMany(ImportCost::class);
    }
}

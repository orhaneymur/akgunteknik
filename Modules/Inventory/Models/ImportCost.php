<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ImportCost extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'cost_type',
        'description',
        'amount',
        'currency',
        'cost_date',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'cost_date' => 'date',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }
}

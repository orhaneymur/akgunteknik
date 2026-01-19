<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'warehouse_id',
        'product_id',
        'quantity',
        'critical_level',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'critical_level' => 'integer',
    ];

    /**
     * Relationship: Inventory level belongs to a warehouse.
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(\Modules\Core\Models\Warehouse::class);
    }

    /**
     * Relationship: Inventory level belongs to a product.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(\Modules\Inventory\Models\Product::class);
    }
}


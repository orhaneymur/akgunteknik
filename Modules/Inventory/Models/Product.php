<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'barcode',
        'purchase_price',
        'sale_price',
        'tax_rate',
        'compatibility',
        'is_active',
    ];

    protected $casts = [
        'purchase_price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'compatibility' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Relationship: Product belongs to a tenant
     * Note: Using DB facade since Core module doesn't have Tenant model yet
     */

    /**
     * Relationship: Product has many inventory levels (stocks) across warehouses
     */
    public function stocks(): HasMany
    {
        return $this->hasMany(InventoryLevel::class, 'product_id');
    }
}


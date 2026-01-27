<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'sku',
        'barcode',
        'description',
        'base_price',
        'is_active',
        'cost_price'
    ];

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function compatibles()
    {
        return $this->belongsToMany(Product::class, 'product_compatibilities', 'product_id', 'compatible_id');
    }

    public function scopeTenant($query)
    {
        return $query->where('tenant_id', auth()->user()->tenant_id ?? null);
    }
}

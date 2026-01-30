<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'brand_id',
        'model_id',
        'name',
        'sku',
        'barcode',
        'description',
        'base_price',
        'is_active',
        'cost_price',
        'tax_rate_id',
    ];

    public function inventoryMovements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function compatibles()
    {
        return $this->belongsToMany(Product::class, 'product_compatibilities', 'product_id', 'compatible_id');
    }

    public function taxRate()
    {
        return $this->belongsTo(\Modules\Core\Models\TaxRate::class);
    }

    public function priceLists()
    {
        return $this->hasMany(ProductPriceList::class);
    }

    public function reservedStock()
    {
        return $this->hasMany(ReservedStock::class);
    }

    public function category()
    {
        return $this->belongsTo(ProductCategory::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function model()
    {
        return $this->belongsTo(ProductModel::class, 'model_id');
    }

    /**
     * Get available stock (current stock - reserved stock)
     */
    public function getAvailableStockAttribute(): int
    {
        $currentStock = $this->inventoryMovements()->sum('quantity') ?? 0;
        $reservedStock = ReservedStock::where('product_id', $this->id)
            ->where('status', 'reserved')
            ->sum('quantity');

        return max(0, $currentStock - $reservedStock);
    }

    public function scopeTenant($query)
    {
        return $query->where('tenant_id', auth()->user()->tenant_id ?? null);
    }
}

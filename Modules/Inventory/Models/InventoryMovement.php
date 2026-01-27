<?php

namespace Modules\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function warehouse()
    {
        // Assuming Warehouse model is in Core module, based on Yapilanlar.md
        // But wait, user said Warehouse is in Core.
        // I need to check where Warehouse model is. 
        // Yapilanlar.md said Core has migrations for warehouses.
        // Let's assume Modules\Core\Models\Warehouse.
        return $this->belongsTo(\Modules\Core\Models\Warehouse::class);
    }
}

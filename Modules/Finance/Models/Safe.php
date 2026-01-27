<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Finance\Models\Transaction;

class Safe extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'currency',
        'balance',
        'iban'
    ];

    protected $casts = [
        'balance' => 'decimal:2',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}

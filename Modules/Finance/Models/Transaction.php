<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Transaction extends Model
{
    protected $fillable = [
        'tenant_id',
        'payable_type',
        'payable_id',
        'safe_id',
        'type',
        'amount',
        'currency',
        'description',
        'date',
        'reference_type',
        'reference_id'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function safe(): BelongsTo
    {
        return $this->belongsTo(Safe::class);
    }
}

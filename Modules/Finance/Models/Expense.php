<?php

namespace Modules\Finance\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    protected $fillable = [
        'tenant_id',
        'description',
        'amount',
        'date',
        'category_id',
        'safe_id',
        'document_path'
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'category_id');
    }

    public function safe(): BelongsTo
    {
        return $this->belongsTo(Safe::class, 'safe_id');
    }
}

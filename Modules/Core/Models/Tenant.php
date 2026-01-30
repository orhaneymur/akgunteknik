<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_name',
        'domain_prefix',
        'is_active',
        'tax_number',
        'tax_office',
        'address',
        'phone',
        'email',
        'website',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}

<?php

namespace Modules\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'currency',
        'rate_date',
        'rate',
        'notes',
    ];

    protected $casts = [
        'rate_date' => 'date',
        'rate' => 'decimal:4',
    ];

    /**
     * Get the latest exchange rate for a currency
     * Rate format: 1 USD = X TRY (for USD currency)
     * 
     * @param string $currency Currency code (USD, EUR, GBP)
     * @param \DateTime|string|null $date Date to get rate for
     * @return float|null Exchange rate or null if not found
     */
    public static function getLatestRate(string $currency, $date = null): ?float
    {
        $date = $date ?? now();
        
        $rate = self::where('currency', $currency)
            ->where('rate_date', '<=', $date)
            ->orderBy('rate_date', 'desc')
            ->first();

        return $rate ? (float) $rate->rate : null;
    }

    /**
     * Get USD to TRY rate (most common use case)
     * 
     * @param \DateTime|string|null $date
     * @return float|null
     */
    public static function getUsdToTryRate($date = null): ?float
    {
        return self::getLatestRate('USD', $date);
    }
}

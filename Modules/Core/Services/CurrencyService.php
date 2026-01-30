<?php

namespace Modules\Core\Services;

use Modules\Core\Models\ExchangeRate;

class CurrencyService
{
    /**
     * Base currency is USD
     */
    const BASE_CURRENCY = 'USD';
    
    /**
     * Display currency is TRY
     */
    const DISPLAY_CURRENCY = 'TRY';

    /**
     * Get latest USD to TRY exchange rate
     * 
     * @param \DateTime|string|null $date
     * @return float|null
     */
    public static function getUsdToTryRate($date = null): ?float
    {
        return ExchangeRate::getLatestRate('USD', $date);
    }

    /**
     * Convert USD to TRY
     * 
     * @param float $usdAmount
     * @param \DateTime|string|null $date
     * @return float|null
     */
    public static function usdToTry(float $usdAmount, $date = null): ?float
    {
        $rate = self::getUsdToTryRate($date);
        if (!$rate) {
            return null;
        }
        return $usdAmount * $rate;
    }

    /**
     * Convert TRY to USD
     * 
     * @param float $tryAmount
     * @param \DateTime|string|null $date
     * @return float|null
     */
    public static function tryToUsd(float $tryAmount, $date = null): ?float
    {
        $rate = self::getUsdToTryRate($date);
        if (!$rate) {
            return null;
        }
        return $tryAmount / $rate;
    }

    /**
     * Format amount in USD with TRY equivalent
     * 
     * @param float $usdAmount
     * @param \DateTime|string|null $date
     * @return array ['usd' => float, 'try' => float|null, 'rate' => float|null]
     */
    public static function formatWithTry(float $usdAmount, $date = null): array
    {
        $rate = self::getUsdToTryRate($date);
        $tryAmount = $rate ? $usdAmount * $rate : null;

        return [
            'usd' => $usdAmount,
            'try' => $tryAmount,
            'rate' => $rate,
        ];
    }

    /**
     * Get currency symbol
     * 
     * @param string $currency
     * @return string
     */
    public static function getSymbol(string $currency): string
    {
        return match($currency) {
            'USD' => '$',
            'TRY' => '₺',
            'EUR' => '€',
            'GBP' => '£',
            default => $currency,
        };
    }
}

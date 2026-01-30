<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Modules\Core\Models\ExchangeRate;

class ExchangeRateApiService
{
    /**
     * TCMB (Türkiye Cumhuriyet Merkez Bankası) API
     * Ücretsiz, resmi kaynak
     * Format: XML
     */
    const TCMB_API_URL = 'https://www.tcmb.gov.tr/kurlar/today.xml';

    /**
     * ExchangeRate-API (Alternatif - Ücretsiz tier)
     * Format: JSON
     */
    const EXCHANGERATE_API_URL = 'https://api.exchangerate-api.com/v4/latest/USD';

    /**
     * Fetch USD to TRY rate from TCMB
     * 
     * @return float|null
     */
    public function fetchFromTcmb(): ?float
    {
        try {
            $response = Http::timeout(10)->get(self::TCMB_API_URL);

            if (!$response->successful()) {
                Log::warning('TCMB API request failed', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return null;
            }

            $xml = simplexml_load_string($response->body());
            
            if (!$xml) {
                Log::error('Failed to parse TCMB XML response');
                return null;
            }

            // TCMB XML formatında USD kodu "USD" olarak geçer
            foreach ($xml->Currency as $currency) {
                $currencyCode = (string) $currency['CurrencyCode'];
                
                if ($currencyCode === 'USD') {
                    // ForexBuying: Döviz Alış (Banknot Alış için ForexBuying kullanılır)
                    // ForexSelling: Döviz Satış
                    // BanknoteBuying: Banknot Alış
                    // BanknoteSelling: Banknot Satış
                    // Genellikle ForexBuying veya BanknoteBuying kullanılır
                    $rate = (string) $currency->ForexBuying;
                    
                    if ($rate && $rate > 0) {
                        return (float) $rate;
                    }
                }
            }

            Log::warning('USD rate not found in TCMB response');
            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching exchange rate from TCMB', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Fetch USD to TRY rate from ExchangeRate-API
     * Note: This API provides USD to other currencies, so we need to calculate TRY
     * 
     * @return float|null
     */
    public function fetchFromExchangeRateApi(): ?float
    {
        try {
            $response = Http::timeout(10)->get(self::EXCHANGERATE_API_URL);

            if (!$response->successful()) {
                Log::warning('ExchangeRate-API request failed', [
                    'status' => $response->status()
                ]);
                return null;
            }

            $data = $response->json();
            
            if (!isset($data['rates']['TRY'])) {
                Log::warning('TRY rate not found in ExchangeRate-API response');
                return null;
            }

            $rate = (float) $data['rates']['TRY'];
            
            if ($rate > 0) {
                return $rate;
            }

            return null;

        } catch (\Exception $e) {
            Log::error('Error fetching exchange rate from ExchangeRate-API', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Fetch USD to TRY rate using multiple sources (fallback)
     * 
     * @return float|null
     */
    public function fetchUsdToTryRate(): ?float
    {
        // Try TCMB first (official source)
        $rate = $this->fetchFromTcmb();
        
        if ($rate) {
            return $rate;
        }

        // Fallback to ExchangeRate-API
        $rate = $this->fetchFromExchangeRateApi();
        
        if ($rate) {
            return $rate;
        }

        Log::error('All exchange rate API sources failed');
        return null;
    }

    /**
     * Save exchange rate to database
     * 
     * @param string $currency
     * @param float $rate
     * @param \DateTime|string|null $date
     * @return ExchangeRate|null
     */
    public function saveRate(string $currency, float $rate, $date = null): ?ExchangeRate
    {
        $date = $date ?? now()->format('Y-m-d');

        // Check if rate already exists for this date
        $existing = ExchangeRate::where('currency', $currency)
            ->where('rate_date', $date)
            ->first();

        if ($existing) {
            $existing->update([
                'rate' => $rate,
                'notes' => 'Otomatik güncellendi (API)'
            ]);
            return $existing;
        }

        return ExchangeRate::create([
            'currency' => $currency,
            'rate_date' => $date,
            'rate' => $rate,
            'notes' => 'Otomatik çekildi (API)'
        ]);
    }

    /**
     * Fetch and save USD to TRY rate
     * 
     * @return ExchangeRate|null
     */
    public function fetchAndSaveUsdRate(): ?ExchangeRate
    {
        $rate = $this->fetchUsdToTryRate();

        if (!$rate) {
            return null;
        }

        return $this->saveRate('USD', $rate);
    }
}

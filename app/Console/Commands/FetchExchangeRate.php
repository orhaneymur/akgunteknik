<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Core\Services\ExchangeRateApiService;

class FetchExchangeRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'exchange-rate:fetch 
                            {--currency=USD : Currency code to fetch (default: USD)}
                            {--force : Force update even if rate exists for today}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch exchange rate from API and save to database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $currency = strtoupper($this->option('currency'));
        $force = $this->option('force');

        $this->info("Fetching {$currency} exchange rate...");

        $service = new ExchangeRateApiService();

        // Check if rate already exists for today (unless force)
        if (!$force && $currency === 'USD') {
            $existing = \Modules\Core\Models\ExchangeRate::where('currency', 'USD')
                ->where('rate_date', now()->format('Y-m-d'))
                ->first();

            if ($existing) {
                $this->warn("Exchange rate for USD already exists for today: {$existing->rate}");
                if (!$this->confirm('Do you want to update it?')) {
                    $this->info('Skipped.');
                    return 0;
                }
            }
        }

        if ($currency === 'USD') {
            $rate = $service->fetchUsdToTryRate();
        } else {
            $this->error("Only USD currency is supported for automatic fetching.");
            return 1;
        }

        if (!$rate) {
            $this->error('Failed to fetch exchange rate from API.');
            return 1;
        }

        $this->info("Fetched rate: 1 USD = {$rate} TRY");

        $saved = $service->saveRate($currency, $rate);

        if ($saved) {
            $this->info("✓ Exchange rate saved successfully!");
            $this->info("  Currency: {$saved->currency}");
            $this->info("  Rate: {$saved->rate}");
            $this->info("  Date: {$saved->rate_date}");
            return 0;
        }

        $this->error('Failed to save exchange rate.');
        return 1;
    }
}

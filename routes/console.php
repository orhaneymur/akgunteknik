<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Scheduled Tasks
Schedule::command('exchange-rate:fetch')
    ->dailyAt('09:00') // Her gün saat 09:00'da çalışır (TCMB genellikle sabah günceller)
    ->timezone('Europe/Istanbul')
    ->withoutOverlapping()
    ->onFailure(function () {
        \Log::error('Exchange rate fetch failed');
    })
    ->onSuccess(function () {
        \Log::info('Exchange rate fetched successfully');
    });

// Alternatif: Her saat başı kontrol et (opsiyonel)
// Schedule::command('exchange-rate:fetch')
//     ->hourly()
//     ->between('09:00', '18:00') // Sadece çalışma saatleri arasında
//     ->timezone('Europe/Istanbul')
//     ->withoutOverlapping();

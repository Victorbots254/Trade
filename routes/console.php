<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-sync live market prices every minute
Schedule::command('market:sync')->everyMinute();

// Automatically process and unlock matured USDT MMF subscriptions every minute
Schedule::command('mmf:process-maturities')->everyMinute();

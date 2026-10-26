<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Jalankan scheduler dengan: php artisan schedule:work (lokal) atau cron `* * * * * php artisan schedule:run`.
Schedule::command('gudang:pantau')->everyFiveMinutes()->withoutOverlapping();

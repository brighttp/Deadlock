<?php

namespace App\Providers;

use App\Services\KursService;
use Illuminate\Support\Carbon;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Satu objek per request, jadi kurs cukup dibaca sekali per request.
        $this->app->singleton(KursService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Nama bulan/hari berbahasa Indonesia di translatedFormat().
        Carbon::setLocale(config('app.locale'));
    }
}

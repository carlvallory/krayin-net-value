<?php

namespace CarlVallory\KrayinNetValue\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;

class KrayinNetValueServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        $this->app->bind(
            \CarlVallory\KrayinNetValue\Services\Bcp\BcpRateFetcher::class,
            \CarlVallory\KrayinNetValue\Services\Bcp\BcpHttpRateFetcher::class
        );

        if ($this->app->runningInConsole()) {
            $this->commands([
                \CarlVallory\KrayinNetValue\Console\Commands\BackfillExchangeRates::class,
                \CarlVallory\KrayinNetValue\Console\Commands\PollExchangeRate::class,
                \CarlVallory\KrayinNetValue\Console\Commands\BackfillLeadsUsd::class,
            ]);
        }

        // El scheduling vive en el paquete (no en el Kernel de la app): producción
        // corre el core upstream de Krayin y todo lo propio entra vía composer.
        $this->app->booted(function () {
            $schedule = $this->app->make(\Illuminate\Console\Scheduling\Schedule::class);

            // Poll de la cotización BCP: solo días hábiles, a la tarde (la referencial
            // cierra después de las 13:00). Tres corridas como reintento por resiliencia.
            $schedule->command('exchange-rates:poll')->weekdays()->at('14:00');
            $schedule->command('exchange-rates:poll')->weekdays()->at('16:00');
            $schedule->command('exchange-rates:poll')->weekdays()->at('18:00');

            // Conversión USD de los leads del año en curso, todas las noches.
            $schedule->command('leads:backfill-usd ' . date('Y'))->dailyAt('02:00');
        });

        // No frontend/translation files needed for this backend backend package.
        
        // Listen to Lead creations and updates to sync net_value
        Event::listen('lead.create.after', 'CarlVallory\KrayinNetValue\Listeners\LeadSaveListener@handle');
        Event::listen('lead.update.after', 'CarlVallory\KrayinNetValue\Listeners\LeadSaveListener@handle');
    }

    // Registration not required

        // No config/menu registration needed.
}

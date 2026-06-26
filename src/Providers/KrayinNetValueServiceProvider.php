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

        // No frontend/translation files needed for this backend backend package.
        
        // Listen to Lead creations and updates to sync net_value
        Event::listen('lead.create.after', 'CarlVallory\KrayinNetValue\Listeners\LeadSaveListener@handle');
        Event::listen('lead.update.after', 'CarlVallory\KrayinNetValue\Listeners\LeadSaveListener@handle');
    }

    // Registration not required

        // No config/menu registration needed.
}

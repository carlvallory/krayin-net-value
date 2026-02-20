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

        $this->loadRoutesFrom(__DIR__ . '/../Routes/web.php');

        $this->loadTranslationsFrom(__DIR__ . '/../Resources/lang', 'krayinnetvalue');

        $this->loadViewsFrom(__DIR__ . '/../Resources/views', 'krayinnetvalue');

        Event::listen('admin.layout.head.after', function($viewRenderEventManager) {
            $viewRenderEventManager->addTemplate('krayinnetvalue::components.layouts.style');
        });
        
        // Listen to Lead creations and updates to sync net_value
        Event::listen('lead.create.after', 'CarlVallory\KrayinNetValue\Listeners\LeadSaveListener@handle');
        Event::listen('lead.update.after', 'CarlVallory\KrayinNetValue\Listeners\LeadSaveListener@handle');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerConfig();
    }

    /**
     * Register package config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/menu.php', 'menu.admin'
        );

        $this->mergeConfigFrom(
            dirname(__DIR__) . '/Config/acl.php', 'acl'
        );
    }
}

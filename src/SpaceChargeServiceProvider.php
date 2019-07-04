<?php

namespace Betalectic\SpaceCharge;

use Illuminate\Support\ServiceProvider;

class SpaceChargeServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
 
    }
}

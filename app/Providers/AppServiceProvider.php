<?php

namespace App\Providers;

use App\Support\PulseRecorders;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Queued jobs on the Telemetry dashboard (laravel-admin-pulse).
        PulseRecorders::register();
    }
}

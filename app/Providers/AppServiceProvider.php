<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The demo resources are registered through App\Admin\DemoPlugin (see
        // config/admin.php → plugins[]).
    }
}

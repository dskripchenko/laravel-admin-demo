<?php

declare(strict_types=1);

namespace App\Providers;

use App\Admin\Resources\ArticleResource;
use App\Admin\Resources\OrderResource;
use App\Admin\Resources\ProductResource;
use Dskripchenko\LaravelAdmin\Facades\Admin;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Регистрируем demo-Resource'ы host-проекта.
        // (Sister-pack Resource'ы регистрируются автоматически через свои plugin'ы.)
        Admin::resources([
            ArticleResource::class,
            ProductResource::class,
            OrderResource::class,
        ]);
    }
}

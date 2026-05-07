<?php

declare(strict_types=1);

namespace App\Admin;

use App\Admin\Resources\ArticleResource;
use App\Admin\Resources\OrderResource;
use App\Admin\Resources\ProductResource;
use App\Admin\Screens\ContentDashboardScreen;
use Dskripchenko\LaravelAdmin\Admin;
use Dskripchenko\LaravelAdmin\Plugin\AdminPlugin;

/**
 * Demo-plugin host-проекта: регистрирует Article/Product/Order Resource'ы.
 *
 * Регистрируется через config('admin.plugins'). Plugin'овая регистрация
 * происходит в AdminServiceProvider::bootPlugins() ДО ResourceCompiler::compile,
 * поэтому HTTP routes для resources доступны после boot.
 *
 * (Альтернативно `Admin::resources([...])` в AppServiceProvider::boot() не
 * работает в текущей версии core — host провайдеры boot'аются ПОСЛЕ admin
 * boot'а. Будет улучшено в core v1.2 — lazy route compilation.)
 */
final class DemoPlugin implements AdminPlugin
{
    public function name(): string
    {
        return 'demo';
    }

    public function version(): string
    {
        return '0.1.0';
    }

    public function register(): void
    {
        // Permissions, settings и т.п. — здесь.
    }

    public function boot(Admin $admin): void
    {
        $admin->resources([
            ArticleResource::class,
            ProductResource::class,
            OrderResource::class,
        ]);
        $admin->screen([
            ContentDashboardScreen::class,
        ]);
    }
}

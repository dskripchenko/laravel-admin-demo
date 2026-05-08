<?php

declare(strict_types=1);

namespace App\Admin;

use App\Admin\Resources\ArticleResource;
use App\Admin\Resources\OrderResource;
use App\Admin\Resources\ProductResource;
use App\Admin\Screens\ContactScreen;
use App\Admin\Screens\ContentDashboardScreen;
use App\Admin\Screens\SystemStatusScreen;
use Dskripchenko\LaravelAdmin\Admin;
use Dskripchenko\LaravelAdmin\Menu\MenuNode;
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
            ContactScreen::class,
            SystemStatusScreen::class,
        ]);

        // Иерархическое меню — демонстрирует indent (depth 0..2) и stripe-mode
        // (depth 3+). withAuto(false) — мы явно описываем всё что должно
        // попасть в sidebar, без auto-fill оставшихся items.
        $admin->menu()->withAuto(false);
        $admin->menu()->add(
            MenuNode::make('content', 'Контент')->icon('book-open')->children([
                MenuNode::resource('articles')->icon('file-text'),
                MenuNode::make('articles-tags', 'Метки')->icon('tag')->url('/r/articles?tag=*')->children([
                    MenuNode::make('articles-tags-tech', 'Tech')->url('/r/articles?tag=tech')->children([
                        MenuNode::make('articles-tags-tech-vue', 'Vue')->url('/r/articles?tag=tech.vue'),
                        MenuNode::make('articles-tags-tech-php', 'PHP')->url('/r/articles?tag=tech.php')->children([
                            MenuNode::make('articles-tags-tech-php-laravel', 'Laravel')->url('/r/articles?tag=tech.php.laravel'),
                        ]),
                    ]),
                    MenuNode::make('articles-tags-news', 'Новости')->url('/r/articles?tag=news'),
                ]),
            ]),
        );
        $admin->menu()->add(
            MenuNode::make('shop', 'Магазин')->icon('shopping-cart')->children([
                MenuNode::resource('products'),
                MenuNode::resource('orders'),
            ]),
        );
        $admin->menu()->add(
            MenuNode::make('analytics', 'Аналитика')->icon('chart-bar')->children([
                MenuNode::dashboard('content'),
            ]),
        );
        $admin->menu()->add(
            MenuNode::make('tools', 'Инструменты')->icon('wrench')->children([
                MenuNode::screen('contact'),
                MenuNode::screen('system-status'),
            ]),
        );
    }
}

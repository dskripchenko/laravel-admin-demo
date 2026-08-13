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
 * The host project's demo plugin: it registers the Article, Product and Order
 * resources.
 *
 * It is registered through config('admin.plugins'). The plugin registration
 * happens in AdminServiceProvider::bootPlugins() BEFORE
 * ResourceCompiler::compile, so the HTTP routes of the resources are available
 * after the boot.
 *
 * (The alternative, `Admin::resources([...])` in AppServiceProvider::boot(),
 * does not work in the current version of the core — the host providers boot
 * AFTER the admin does. This will be improved in core v1.2, with lazy route
 * compilation.)
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
        // Permissions, settings and the like go here.
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

        // A hierarchical menu — it demonstrates the indentation (depth 0..2)
        // and the stripe mode (depth 3 and deeper). withAuto(false) means we
        // describe everything that should reach the sidebar explicitly, without
        // auto-filling the remaining items.
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

<?php

namespace App\Providers;

use App\Admin\Dashboards\ShopDashboard;
use App\Admin\Resources\Blog\AuthorResource;
use App\Admin\Resources\Blog\BlogCategoryResource;
use App\Admin\Resources\Blog\PostResource;
use App\Admin\Resources\Blog\TagResource;
use App\Admin\Resources\Shop\CustomerResource;
use App\Admin\Resources\Shop\OrderResource;
use App\Admin\Resources\Shop\ProductCategoryResource;
use App\Admin\Resources\Shop\ProductResource;
use App\Admin\Screens\Docs\DocsHomeScreen;
use App\Admin\Showcase\Forms\FormBasicsScreen;
use App\Admin\Showcase\Layouts\ColumnsScreen;
use App\Admin\Showcase\Layouts\TabsScreen;
use App\Admin\Showcase\ShowcaseHomeScreen;
use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
use Dskripchenko\LaravelAdmin\Facades\Admin;
use Dskripchenko\LaravelAdmin\Menu\MenuNode;
use Illuminate\Support\ServiceProvider;

/**
 * Everything the panel shows: the demo shop and blog, the showcase, the
 * documentation and the system sections of the sister packages — and the
 * menu that ties them together.
 */
class AdminServiceProvider extends ServiceProvider
{
    /** Showcase screens, grouped in the menu by their group(). */
    public const SHOWCASE = [
        FormBasicsScreen::class,
        TabsScreen::class,
        ColumnsScreen::class,
    ];

    public function register(): void
    {
        $this->app->singleton(DocsCatalog::class);
        $this->app->singleton(DocsLibrary::class);
    }

    public function boot(): void
    {
        Admin::resources([
            ProductResource::class,
            ProductCategoryResource::class,
            OrderResource::class,
            CustomerResource::class,
            PostResource::class,
            BlogCategoryResource::class,
            TagResource::class,
            AuthorResource::class,
        ]);

        Admin::screen([
            ShopDashboard::class,
            ShowcaseHomeScreen::class,
            DocsHomeScreen::class,
            ...self::SHOWCASE,
            ...$this->app->make(DocsCatalog::class)->screens(),
        ]);

        // Built once every provider has booted, replacing the entries the sister
        // packs add on their own: this stand arranges the whole menu itself.
        $this->app->booted(fn () => $this->menu());
    }

    private function menu(): void
    {
        Admin::menu()->clear()->withAuto(false)
            ->add(MenuNode::dashboard('main')->label('Overview')->icon('layout-dashboard'))
            ->add($this->showcaseMenu())
            ->add(MenuNode::make('shop', 'Shop')->icon('store')->children([
                MenuNode::resource('orders'),
                MenuNode::resource('products'),
                MenuNode::resource('product-categories'),
                MenuNode::resource('customers'),
            ]))
            ->add(MenuNode::make('blog', 'Blog')->icon('newspaper')->children([
                MenuNode::resource('posts'),
                MenuNode::resource('blog-categories'),
                MenuNode::resource('tags'),
                MenuNode::resource('authors'),
                MenuNode::resource('media-library')->label('Media library'),
            ]))
            ->add($this->docsMenu())
            ->add(MenuNode::make('system', 'System')->icon('settings')->children([
                MenuNode::resource('system-users'),
                MenuNode::resource('system-roles'),
                MenuNode::resource('system-audit'),
                MenuNode::resource('system-health-results')->label('Health')->icon('health-check'),
                MenuNode::make('jobs', 'Jobs')->icon('list-checks')->children([
                    MenuNode::resource('system-failed-jobs'),
                    MenuNode::resource('system-job-batches'),
                ]),
                MenuNode::dashboard('telemetry')->label('Telemetry')->icon('activity'),
                MenuNode::resource('system-pulse-samples'),
            ]));
    }

    private function showcaseMenu(): MenuNode
    {
        $children = [MenuNode::screen('showcase')->label('About the showcase')->icon('info')];
        foreach (ShowcaseHomeScreen::GROUPS as $key => [$title]) {
            $group = MenuNode::make('showcase-'.$key, $title)->icon(self::SHOWCASE_ICONS[$key]);
            $screens = array_filter(self::SHOWCASE, fn (string $screen) => $screen::group() === $key);
            $nodes = array_map(fn (string $screen) => MenuNode::screen($screen::slug()), array_values($screens));
            // Until a group has examples of its own, it points at the live
            // sections that already show the feature.
            $nodes = $nodes !== [] ? $nodes : self::showcaseFallback($key);
            $children[] = $group->children($nodes);
        }

        return MenuNode::make('showcase', 'Showcase')->icon('sparkles')->children($children);
    }

    private const SHOWCASE_ICONS = [
        'dashboards' => 'layout-dashboard',
        'forms' => 'clipboard-list',
        'grids' => 'table',
        'layouts' => 'columns',
        'actions' => 'zap',
        'navigation' => 'map',
        'notifications' => 'bell',
    ];

    /** @return list<MenuNode> */
    private static function showcaseFallback(string $group): array
    {
        return match ($group) {
            'dashboards' => [
                MenuNode::dashboard('main')->label('Shop overview')->icon('store'),
                MenuNode::dashboard('telemetry')->label('Telemetry')->icon('activity'),
            ],
            'grids' => [
                MenuNode::resource('orders')->label('Orders: filters, summary, bulk actions')->icon('shopping-cart'),
                MenuNode::resource('products')->label('Products: images, inline editing')->icon('package'),
            ],
            'actions' => [MenuNode::resource('orders')->label('Orders: status flow')->icon('workflow')],
            'navigation' => [MenuNode::resource('product-categories')->label('Category tree')->icon('folder-tree')],
            default => [MenuNode::screen('showcase')->label('Coming soon')->icon('clock')],
        };
    }

    private function docsMenu(): MenuNode
    {
        $top = [MenuNode::screen('docs')->label('Contents')->icon('book-open')];
        $groups = [];
        foreach (DocsCatalog::PAGES as $entry) {
            $node = MenuNode::screen(DocsCatalog::slugFor($entry['page']))->label($entry['title'])->icon($entry['icon']);
            if ($entry['group'] === null) {
                $top[] = $node;
            } else {
                $groups[$entry['group']][] = $node;
            }
        }
        foreach (DocsCatalog::GROUPS as $group => $icon) {
            $top[] = MenuNode::make('docs-group-'.strtolower($group), $group)->icon($icon)->children($groups[$group] ?? []);
        }

        return MenuNode::make('docs', 'Docs')->icon('book-open')->children($top);
    }
}

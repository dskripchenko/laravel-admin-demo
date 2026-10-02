<?php

namespace App\Admin\Showcase\Navigation;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Menu\MenuNode;

/**
 * Navigation › Menu tree: a sidebar branch five levels deep, declared with
 * MenuNode. The branch itself is the example — open "Nested menu" in the
 * sidebar.
 */
final class MenuTreeScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-navigation-menu';
    }

    public static function group(): string
    {
        return 'navigation';
    }

    public static function icon(): string
    {
        return 'list-tree';
    }

    public function name(): string
    {
        return 'Menu tree';
    }

    public function description(): ?string
    {
        return 'Admin::menu() with MenuNode — any depth, built from resources, screens and plain URLs.';
    }

    protected function demo(): array
    {
        return [
            Layout::markdown(__(<<<'MD'
The sidebar is a tree of `MenuNode`s of any depth. Open **Showcase › Navigation › Nested menu** in the sidebar: the branch below it is declared by `tree()` on this page.

| Factory | What it fills in |
|---|---|
| `MenuNode::resource('orders')` | Label, icon, `/r/orders` and the `admin.orders.view` permission, from the resource |
| `MenuNode::screen('docs')` | Label and `/screens/docs` from the screen; a dashboard gets `/dashboard/{slug}` |
| `MenuNode::dashboard('main')` | The same, for a dashboard explicitly |
| `MenuNode::make('key', 'Label')` | A plain node: a group with `children()`, or a link with `url()` — a query string included |

What the panel does with the tree:

- the levels indent up to the third one, deeper levels get a coloured stripe instead;
- the branch of the current page opens by itself, so a deep link lands with its parents expanded;
- a node the user may not open (its `permissions()`, or the resource's view permission) is hidden, and a parent with no visible children disappears with them — sign in as the Editor and the Orders entry is gone.
MD))->card(),
        ];
    }

    /**
     * The "Nested menu" branch of the sidebar, added by NavigationGroup::menu().
     */
    public static function tree(): MenuNode
    {
        return MenuNode::make('showcase-nested', 'Nested menu')->icon('list-tree')->children([
            MenuNode::make('showcase-nested-shop', 'L2 · Shop')->icon('store')->children([
                MenuNode::resource('orders')->label('Orders')->badge(MenuBadgesScreen::pendingOrders()),
                MenuNode::make('showcase-nested-paid', 'Paid orders')
                    ->icon('filter')
                    ->url('/screens/'.QueryParamsScreen::slug().'?tab=list&status=paid'),
                MenuNode::make('showcase-nested-catalog', 'L3 · Catalog')->icon('folder')->children([
                    MenuNode::resource('products')->label('Products'),
                    MenuNode::make('showcase-nested-new-product', 'New product, prefilled')
                        ->icon('plus')
                        ->url('/r/products/create?status=draft&is_featured=1')
                        ->permissions('admin.products.create'),
                    MenuNode::make('showcase-nested-categories', 'L4 · Categories')->icon('folder-tree')->children([
                        MenuNode::resource('product-categories')->label('Category tree'),
                        MenuNode::make('showcase-nested-deep', 'L5 · Deeper')->icon('layers')->children([
                            MenuNode::screen(self::slug())->label('Back to Menu tree')->icon('rotate-ccw'),
                        ]),
                    ]),
                ]),
            ]),
            MenuNode::make('showcase-nested-blog', 'L2 · Blog')->icon('newspaper')->children([
                MenuNode::resource('posts')->label('Posts'),
                MenuNode::screen('docs-concepts-menu')->label('Docs: the menu')->icon('book-open'),
            ]),
        ]);
    }

    protected function sourceMethod(): ?string
    {
        return 'tree';
    }

    protected function sourceClasses(): array
    {
        return [NavigationGroup::class];
    }
}

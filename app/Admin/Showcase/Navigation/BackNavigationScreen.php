<?php

namespace App\Admin\Showcase\Navigation;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Navigation › Where am I: how the panel shows the current place and the
 * way back — the active branch of the sidebar, the page header and the
 * Back link of a record's form.
 */
final class BackNavigationScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-navigation-back';
    }

    public static function group(): string
    {
        return 'navigation';
    }

    public static function icon(): string
    {
        return 'map-pin';
    }

    public function name(): string
    {
        return 'Where am I';
    }

    public function description(): ?string
    {
        return 'The active menu branch, page headers and the Back link of record pages.';
    }

    protected function demo(): array
    {
        return [
            Layout::markdown(__(<<<'MD'
The panel has no breadcrumb trail in the top bar. The place in the panel is shown by three things instead:

1. **The sidebar.** The entry of the current page is highlighted and its whole branch is open, however deep — follow a link to a page from "Nested menu" and the branch unfolds by itself.
2. **The page header.** `name()` and `description()` of a screen, the resource label and the record title on record pages.
3. **The Back link of a record page.** The edit and view pages of a record start with "← Back", which leads to the resource list. A resource shown as a leaf of another resource's tree overrides `parentSlug()`, and Back returns to that tree instead.

The buttons above open pages that show it: record pages with their Back link, and the category tree.
MD))->card(),
        ];
    }

    public function commandBar(): array
    {
        $admin = '/'.trim((string) config('admin.path', 'admin'), '/');

        return [
            Link::make('View a product')->href($admin.'/r/products/1')->icon('package')
                ->permission('admin.products.view'),
            Link::make('View an order')->href($admin.'/r/orders/1')->icon('eye')
                ->permission('admin.orders.view'),
            Link::make('Category tree')->href($admin.'/r/product-categories')->icon('folder-tree')
                ->permission('admin.product-categories.view'),
        ];
    }
}

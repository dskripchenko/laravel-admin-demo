<?php

namespace App\Admin\Showcase\Navigation;

use App\Admin\Showcase\ShowcaseScreen;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Action\DropDown;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Navigation › Links: from a screen to other screens, to resource lists, to
 * single records and out of the panel — as buttons, in Markdown and in table
 * cells.
 */
final class LinksScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-navigation-links';
    }

    public static function group(): string
    {
        return 'navigation';
    }

    public static function icon(): string
    {
        return 'link';
    }

    public function name(): string
    {
        return 'Links';
    }

    public function description(): ?string
    {
        return 'Link actions, Markdown links and linked table cells.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'orders' => Order::query()->with('customer')->latest('placed_at')->limit(5)->get()
                ->map(fn (Order $order) => [
                    'id' => $order->id,
                    'number' => $order->number,
                    'customer' => $order->customer_name,
                    'total' => $order->total,
                ])->all(),
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('In Markdown', [
                    Layout::markdown(__(<<<'MD'
Relative links resolve against `linkBase()`, and links inside the panel open without a page reload:

- [Form basics](showcase-forms-basics) — another showcase screen;
- [Paid orders](showcase-navigation-query?tab=list&status=paid) — a screen with a query string;
- [Menu concept](docs-concepts-menu#explicit-hierarchy) — a docs page, scrolled to a heading;
- [laravel-admin on GitHub](https://github.com/dskripchenko/laravel-admin) — outside the panel, in a new tab.
MD))->linkBase('/'.trim((string) config('admin.path', 'admin'), '/').'/screens/'),
                ])->icon('file-text'),
                Layout::block('In a table', [
                    RelationTable::make('orders')->title('Latest orders')->columns([
                        TableColumn::make('number')->label('Order')
                            ->asLink('/'.trim((string) config('admin.path', 'admin'), '/').'/r/orders/{id}'),
                        TableColumn::make('customer')->label('Customer'),
                        TableColumn::make('total')->asMoney('USD')->align('right'),
                    ]),
                ])->icon('table')->description('asLink() builds the address from the row: {id} is the row\'s id.'),
            ]),
        ];
    }

    public function commandBar(): array
    {
        $admin = '/'.trim((string) config('admin.path', 'admin'), '/');

        return [
            Link::make('Another screen')->href($admin.'/screens/showcase-forms-basics')->icon('monitor'),
            Link::make('A list')->href($admin.'/r/orders')->icon('list')
                ->permission('admin.orders.view'),
            DropDown::make('A record')->icon('file')->items([
                Link::make('View order #1')->href($admin.'/r/orders/1')->permission('admin.orders.view'),
                Link::make('Edit product #1')->href($admin.'/r/products/1/edit')->permission('admin.products.update'),
                Link::make('New product, prefilled')->href($admin.'/r/products/create?status=draft&is_featured=1')
                    ->permission('admin.products.create'),
            ]),
            Link::make('GitHub')->href('https://github.com/dskripchenko/laravel-admin')->target('_blank')->icon('globe'),
        ];
    }
}

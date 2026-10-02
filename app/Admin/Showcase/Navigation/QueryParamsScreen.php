<?php

namespace App\Admin\Showcase\Navigation;

use App\Admin\Showcase\ShowcaseScreen;
use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Action\DropDown;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Infolist\BadgeEntry;
use Dskripchenko\LaravelAdmin\Infolist\TextEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Navigation › Query parameters: a screen that opens on what its address
 * names — a tab, a status filter, a record:
 * /admin/screens/showcase-navigation-query?tab=list&status=paid.
 */
final class QueryParamsScreen extends ShowcaseScreen
{
    private const TABS = ['order', 'list'];

    public static function slug(): string
    {
        return 'showcase-navigation-query';
    }

    public static function group(): string
    {
        return 'navigation';
    }

    public static function icon(): string
    {
        return 'search';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Query parameters';
    }

    public function description(): ?string
    {
        return 'The address picks the tab, the filter and the record: ?tab=list&status=paid.';
    }

    /**
     * The query string arrives positionally; by name it is read from the
     * request. It comes from the address bar — validate it like any input.
     */
    public function query(mixed ...$params): array
    {
        $number = (string) request()->query('order', 'ORD-10001');
        $order = preg_match('/^ORD-\d+$/', $number)
            ? Order::query()->with('customer')->where('number', $number)->first()
            : null;
        $status = OrderStatus::tryFrom((string) request()->query('status', '')) ?? OrderStatus::Pending;

        return [
            'number' => $order?->number ?? '—',
            'customer' => $order?->customer_name ?? '—',
            'status' => $order?->status->value,
            'total' => $order?->total,
            'placed_at' => $order?->placed_at?->toDateTimeString(),
            'filter' => $status->label(),
            'orders' => Order::query()->with('customer')->where('status', $status->value)
                ->latest('placed_at')->limit(8)->get()
                ->map(fn (Order $row) => [
                    'id' => $row->id,
                    'number' => $row->number,
                    'customer' => $row->customer_name,
                    'total' => $row->total,
                    'placed_at' => $row->placed_at?->toDateTimeString(),
                ])->all(),
        ];
    }

    protected function demo(): array
    {
        $tab = array_search(request()->query('tab'), self::TABS, true);

        return [
            Layout::markdown(__(<<<'MD'
Everything below follows the address of the page. The buttons above only change the query string — `?order=ORD-10002`, `?tab=list&status=paid` — and the panel loads the screen's state for it; the browser's Back button and a reload keep it.

`query()` receives the values positionally, in the order of the query string; read them by name with `request()->query('status')` and validate them like any other input. The layout can use them too: `Tabs::defaultTab()` opens the tab named by `?tab=`.
MD))->card(),
            Layout::tabs([
                'Order from ?order=' => [
                    Layout::infolist([
                        TextEntry::make('number')->label('Order')->copyable(),
                        TextEntry::make('customer')->label('Customer'),
                        BadgeEntry::make('status')->label('Status')->colors(OrderStatus::colors())->labels(OrderStatus::options()),
                        TextEntry::make('total')->label('Total')->asMoney('USD'),
                        TextEntry::make('placed_at')->label('Placed')->asDateTime(),
                    ])->layout('grid'),
                ],
                'Orders by ?status=' => [
                    Layout::infolist([TextEntry::make('filter')->label('Status')]),
                    RelationTable::make('orders')->title('Latest eight')->columns([
                        TableColumn::make('number')->label('Order'),
                        TableColumn::make('customer')->label('Customer'),
                        TableColumn::make('total')->asMoney('USD')->align('right'),
                        TableColumn::make('placed_at')->label('Placed')->asDateTime(),
                    ]),
                ],
            ])->defaultTab($tab === false ? 0 : $tab),
        ];
    }

    public function commandBar(): array
    {
        $base = '/'.trim((string) config('admin.path', 'admin'), '/').'/screens/'.self::slug();

        return [
            DropDown::make('Open an order')->icon('receipt')->items(array_map(
                fn (string $number) => Link::make($number)->withName('order-'.strtolower($number))->href($base.'?tab=order&order='.$number),
                ['ORD-10001', 'ORD-10002', 'ORD-10003'],
            )),
            DropDown::make('Filter by status')->icon('filter')->items(array_map(
                fn (OrderStatus $status) => Link::make($status->label())->withName('status-'.$status->value)->href($base.'?tab=list&status='.$status->value),
                OrderStatus::cases(),
            )),
        ];
    }
}

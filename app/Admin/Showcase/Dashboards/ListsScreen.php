<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\ShowcaseScreen;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use Carbon\CarbonInterface;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Dskripchenko\LaravelAdmin\Widget\HeatmapWidget;
use Dskripchenko\LaravelAdmin\Widget\IframeWidget;
use Dskripchenko\LaravelAdmin\Widget\MarkdownWidget;
use Dskripchenko\LaravelAdmin\Widget\RecentListWidget;
use Dskripchenko\LaravelAdmin\Widget\TableWidget;
use Illuminate\Support\Str;

/**
 * Dashboards › Tables, lists and text: the widgets that show records, a
 * matrix, prose and an embedded page.
 */
final class ListsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-dashboards-lists';
    }

    public static function group(): string
    {
        return 'dashboards';
    }

    public static function icon(): string
    {
        return 'list';
    }

    public function name(): string
    {
        return 'Tables, lists and text';
    }

    public function description(): ?string
    {
        return 'TableWidget, RecentListWidget, HeatmapWidget, MarkdownWidget and IframeWidget.';
    }

    protected function demo(): array
    {
        return [
            Layout::dashboard([
                // The latest records of a model; a row opens the record in its resource.
                // A column may be an accessor or a relation path, customer.name here,
                // and a TableColumn formats like a resource list: money, dates, badges.
                RecentListWidget::make()->title('Latest orders')->size(6)->rowSpan(3)
                    ->model(Order::class)->orderBy('placed_at', 'desc')->limit(6)
                    ->column('number', 'Order')->column('customer.name', 'Customer')
                    ->column(TableColumn::make('placed_at')->label('Placed')->asDate('d.m.Y'))
                    ->column(TableColumn::make('total')->label('Total')->asMoney('USD')->align('right'))
                    ->linkTo('orders'),

                // Any query, with the column presets of a resource list.
                TableWidget::make()->title('Biggest customers')->size(6)->rowSpan(3)
                    ->model(Customer::class)
                    ->query(fn ($query) => $query->withSum('orders', 'total'))
                    ->orderBy('orders_sum_total', 'desc')->limit(6)
                    ->columns([
                        TableColumn::make('name')->label('Customer'),
                        TableColumn::make('city')->label('City'),
                        TableColumn::make('orders_sum_total')->label('Spent')->asMoney('USD')->align('right'),
                    ]),

                // A rows × columns matrix of values, every column labelled. Each
                // cell is the average orders per day, so the current month, only
                // partly over, compares with the full ones; a weekday the month has
                // not had yet is null, an empty "no data" cell.
                HeatmapWidget::make()->title('Orders per day by weekday and month')->size(8)->rowSpan(2)
                    ->axes(...$this->heatmapAxes())
                    ->matrix($this->heatmapMatrix())
                    ->colorScale('viridis'),

                // Static rich text.
                MarkdownWidget::make()->title('Notes')->size(4)->rowSpan(2)->content(__(<<<'MD'
                    **MarkdownWidget** shows static text: release notes, a checklist, links.

                    - Lists and *emphasis*
                    - `inline code`
                    - [The docs](/admin/screens/docs-concepts-widgets-and-dashboards)
                    MD)),

                // Another page in a sandboxed frame — here, this site's landing
                // page, its scripts kept in an origin of their own.
                IframeWidget::make()->title('An embedded page')->size(12)->rowSpan(3)
                    ->src('/?lang='.app()->getLocale())
                    ->sandbox('allow-scripts'),
            ]),
        ];
    }

    /** @return array{list<string>, list<string>} */
    private function heatmapAxes(): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        // Month names in the panel's language: May, Jun… or Май, Июн…
        $months = collect(range(5, 0))
            ->map(fn (int $i) => Str::ucfirst(now()->locale(app()->getLocale())->startOfMonth()->subMonths($i)->translatedFormat('M')))
            ->all();

        return [$days, $months];
    }

    /**
     * The average orders per day for each weekday × month over the last six
     * months, counting whole days only; null where the month has not had that
     * weekday yet.
     *
     * @return list<list<float|null>>
     */
    private function heatmapMatrix(): array
    {
        $today = now()->startOfDay();
        $from = $today->copy()->startOfMonth()->subMonths(5);
        $column = fn (CarbonInterface $date): int => min(5, (int) $from->diffInMonths($date->copy()->startOfMonth()));

        $days = array_fill(0, 7, array_fill(0, 6, 0));
        for ($day = $from->copy(); $day->lt($today); $day->addDay()) {
            $days[$day->dayOfWeekIso - 1][$column($day)]++;
        }

        $orders = array_fill(0, 7, array_fill(0, 6, 0));
        Order::query()->where('placed_at', '>=', $from)->where('placed_at', '<', $today)->get(['placed_at'])
            ->each(function (Order $order) use (&$orders, $column): void {
                $orders[$order->placed_at->dayOfWeekIso - 1][$column($order->placed_at)]++;
            });

        return array_map(
            fn (array $counts, array $seen): array => array_map(
                fn (int $count, int $n): ?float => $n === 0 ? null : round($count / $n, 1),
                $counts,
                $seen,
            ),
            $orders,
            $days,
        );
    }
}

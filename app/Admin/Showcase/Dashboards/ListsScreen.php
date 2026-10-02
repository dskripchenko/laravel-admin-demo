<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\ShowcaseScreen;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Dskripchenko\LaravelAdmin\Widget\HeatmapWidget;
use Dskripchenko\LaravelAdmin\Widget\IframeWidget;
use Dskripchenko\LaravelAdmin\Widget\MarkdownWidget;
use Dskripchenko\LaravelAdmin\Widget\RecentListWidget;
use Dskripchenko\LaravelAdmin\Widget\TableWidget;

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
                // A column may be an accessor or a relation path, customer.name here.
                RecentListWidget::make()->title('Latest orders')->size(6)->rowSpan(3)
                    ->model(Order::class)->orderBy('placed_at', 'desc')->limit(6)
                    ->column('number', 'Order')->column('customer.name', 'Customer')->column('total', 'Total')
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

                // A rows × columns matrix of values.
                HeatmapWidget::make()->title('Orders by weekday and month')->size(8)->rowSpan(2)
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
        $months = collect(range(5, 0))->map(fn (int $i) => now()->startOfMonth()->subMonths($i)->translatedFormat('M'))->all();

        return [$days, $months];
    }

    /** @return list<list<int>> */
    private function heatmapMatrix(): array
    {
        $from = now()->startOfMonth()->subMonths(5);
        $matrix = array_fill(0, 7, array_fill(0, 6, 0));
        Order::query()->where('placed_at', '>=', $from)->get(['placed_at'])
            ->each(function (Order $order) use (&$matrix, $from): void {
                $month = (int) $from->diffInMonths($order->placed_at->copy()->startOfMonth());
                $matrix[$order->placed_at->dayOfWeekIso - 1][min(5, $month)]++;
            });

        return $matrix;
    }
}

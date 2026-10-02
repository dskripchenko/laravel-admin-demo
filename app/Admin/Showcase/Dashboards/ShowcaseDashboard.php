<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\Dashboards\Widgets\AverageBasketWidget;
use App\Admin\Showcase\Dashboards\Widgets\CustomerSegmentsWidget;
use App\Admin\Showcase\Dashboards\Widgets\LiveOrdersWidget;
use App\Admin\Showcase\Dashboards\Widgets\PaymentMethodsWidget;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Dskripchenko\LaravelAdmin\Widget\DashboardScreen;
use Dskripchenko\LaravelAdmin\Widget\MarkdownWidget;
use Dskripchenko\LaravelAdmin\Widget\RecentListWidget;

/**
 * A dashboard screen of its own: /admin/dashboard/showcase-dashboard.
 *
 *  - periods() and defaultPeriod() set the period switcher;
 *  - a widget with ->refresh() re-reads its data on a timer;
 *  - a widget with ->permission() is shown only to users who hold it;
 *  - "Edit" in the toolbar lets every user rearrange, resize, hide and add
 *    widgets; the layout is saved per user, on top of this canonical one.
 *
 * A saved per-user layout refers to widgets by slug: a widget class has its
 * own, and several instances of one class — the two charts below — are named
 * with ->withSlug().
 */
final class ShowcaseDashboard extends DashboardScreen
{
    public static function slug(): string
    {
        return 'showcase-dashboard';
    }

    public function name(): string
    {
        return 'Showcase dashboard';
    }

    public function periods(): ?array
    {
        return ['7d', '30d', '90d', 'all'];
    }

    public function defaultPeriod(): string
    {
        return '90d';
    }

    public function widgets(): array
    {
        [$weekdays, $averages] = $this->weekdays();

        return [
            AverageBasketWidget::make()->title('Baskets in the period')->size(12),
            LiveOrdersWidget::make()->title('Live, every 15 seconds')->size(4)->refresh(15),
            PaymentMethodsWidget::make()->title('Payment methods')->size(4)->rowSpan(3),
            CustomerSegmentsWidget::make()->title('Customer segments')->size(4)->rowSpan(3)
                ->permission('admin.customers.view'),
            ChartWidget::make()->withSlug('orders-by-weekday')->title('Orders by weekday')->size(6)->rowSpan(2)
                ->chartType('bar')->labels(array_map('__', array_keys($weekdays)))
                ->dataset('Orders', array_values($weekdays), '#6366f1'),
            ChartWidget::make()->withSlug('average-by-weekday')->title('Average order by weekday')->size(6)->rowSpan(2)
                ->chartType('line')->labels(array_map('__', array_keys($averages)))
                ->dataset('Average order, $', array_values($averages), '#10b981'),
            RecentListWidget::make()->title('Recently updated products')->size(12)->rowSpan(3)
                ->model(Product::class)->orderBy('updated_at', 'desc')->limit(6)
                ->column('name', 'Product')->column('stock', 'Stock')
                ->linkTo('products'),
            MarkdownWidget::make()->title('Try it')->size(12)->rowSpan(1)->content(fn () => __(
                'Switch the period, press **Edit** to drag, resize or hide widgets and add your own — the layout is saved for your account only.'
            )),
        ];
    }

    /**
     * Orders and the average order per weekday over the last 90 days, grouped
     * in PHP so the same code runs on every database.
     *
     * @return array{array<string, int>, array<string, float>}
     */
    private function weekdays(): array
    {
        $names = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $counts = array_fill_keys($names, 0);
        $sums = array_fill_keys($names, 0.0);
        Order::query()->where('placed_at', '>=', now()->subDays(90))->get(['placed_at', 'total'])
            ->each(function (Order $order) use (&$counts, &$sums, $names): void {
                $day = $names[$order->placed_at->dayOfWeekIso - 1];
                $counts[$day]++;
                $sums[$day] += (float) $order->total;
            });

        $averages = [];
        foreach ($names as $day) {
            $averages[$day] = $counts[$day] > 0 ? round($sums[$day] / $counts[$day], 2) : 0.0;
        }

        return [$counts, $averages];
    }
}

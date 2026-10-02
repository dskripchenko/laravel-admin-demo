<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\ShowcaseScreen;
use App\Enums\OrderStatus;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Widget\GaugeWidget;
use Dskripchenko\LaravelAdmin\Widget\StatsOverviewWidget;

/**
 * Dashboards › KPIs and gauges: stat cards with colours, icons and trends,
 * and gauges with coloured thresholds. Widgets are not tied to a dashboard
 * screen: Layout::dashboard() puts the same grid on any screen.
 */
final class KpiScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-dashboards-kpi';
    }

    public static function group(): string
    {
        return 'dashboards';
    }

    public static function icon(): string
    {
        return 'gauge';
    }

    public function name(): string
    {
        return 'KPIs and gauges';
    }

    public function description(): ?string
    {
        return 'StatsOverviewWidget cards with trends, GaugeWidget with thresholds.';
    }

    protected function demo(): array
    {
        $revenue = (float) Order::query()->whereIn('status', OrderStatus::revenue())->sum('total');
        $orders = Order::query()->count();
        $closed = Order::query()->whereIn('status', ['delivered', 'cancelled', 'refunded'])->count();
        $delivered = Order::query()->where('status', 'delivered')->count();
        $inStock = Product::query()->where('stock', '>', 0)->count();

        return [
            Layout::dashboard([
                // One card: a label and a value.
                StatsOverviewWidget::make()->title('A single value')->size(3)
                    ->stat(__('Customers'), Customer::query()->count()),

                // Several cards in one widget, each with a colour, an icon and a trend.
                StatsOverviewWidget::make()->title('Cards with colours, icons and trends')->size(9)
                    ->stat(__('Revenue'), '$'.number_format($revenue), 'green', 'dollar-sign')->trend(12.5, 'up')
                    ->stat(__('Orders'), number_format($orders), 'blue', 'shopping-cart')->trend(3.1, 'up')
                    ->stat(__('Refunds'), Order::query()->where('status', 'refunded')->count(), 'red', 'rotate-ccw')->trend(1.4, 'down'),

                // A gauge: a value on a range over coloured zones (any CSS colour).
                GaugeWidget::make()->title('Delivered of closed orders')->size(4)->rowSpan(2)
                    ->value($closed > 0 ? round($delivered / $closed * 100, 1) : 0)
                    ->range(0, 100)->unit('%')
                    ->threshold(0, 80, '#ef4444')->threshold(80, 90, '#f59e0b')->threshold(90, 100, '#10b981'),

                GaugeWidget::make()->title('Products in stock')->size(4)->rowSpan(2)
                    ->value($inStock)
                    ->range(0, Product::query()->count())
                    ->threshold(0, 150, '#f59e0b')->threshold(150, 1000, '#10b981'),

                GaugeWidget::make()->title('Server load')->size(4)->rowSpan(2)
                    ->value(42)->range(0, 100)->unit('%')
                    ->threshold(0, 60, '#10b981')->threshold(60, 85, '#f59e0b')->threshold(85, 100, '#ef4444'),
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

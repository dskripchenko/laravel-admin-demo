<?php

namespace App\Admin\Dashboards;

use App\Admin\Widgets\Shop\FulfilmentGaugeWidget;
use App\Admin\Widgets\Shop\LowStockWidget;
use App\Admin\Widgets\Shop\OrderHeatmapWidget;
use App\Admin\Widgets\Shop\OrderStatusWidget;
use App\Admin\Widgets\Shop\RevenueChartWidget;
use App\Admin\Widgets\Shop\SalesKpiWidget;
use App\Admin\Widgets\Shop\TopProductsWidget;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\DashboardScreen;
use Dskripchenko\LaravelAdmin\Widget\RecentListWidget;

/**
 * The home page: how the demo shop is doing. Every number is computed inside
 * a widget's data(), so the period switcher re-queries only what it changes.
 * A widget class per widget keeps the slugs distinct — that is what a saved
 * per-user layout is keyed by.
 */
final class ShopDashboard extends DashboardScreen
{
    public static function slug(): string
    {
        return 'main';
    }

    public function name(): string
    {
        return 'Shop overview';
    }

    public function periods(): ?array
    {
        return ['7d', '30d', '90d', 'all'];
    }

    public function defaultPeriod(): string
    {
        return '30d';
    }

    public function widgets(): array
    {
        return [
            SalesKpiWidget::make()->title('Sales')->size(12)->periodAware(),
            RevenueChartWidget::make()->title('Revenue, last 12 months')->size(8)->rowSpan(3),
            OrderStatusWidget::make()->title('Orders by status')->size(4)->rowSpan(3)->periodAware(),
            TopProductsWidget::make()->title('Best sellers')->size(6)->rowSpan(3)->periodAware(),
            RecentListWidget::make()->title('Latest orders')->size(6)->rowSpan(3)
                ->model(Order::class)->orderBy('placed_at', 'desc')->limit(8)
                ->column('number', 'Order')->column('customer_name', 'Customer')->column('total', 'Total')
                ->linkTo('orders'),
            OrderHeatmapWidget::make()->title('When customers order')->size(8)->rowSpan(2)->periodAware(),
            FulfilmentGaugeWidget::make()->title('Delivered of closed orders')->size(4)->rowSpan(2)->periodAware(),
            LowStockWidget::make()->title('Running low')->size(12)->rowSpan(2),
        ];
    }
}

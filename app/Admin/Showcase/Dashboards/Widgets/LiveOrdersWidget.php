<?php

namespace App\Admin\Showcase\Dashboards\Widgets;

use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\StatsOverviewWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/**
 * Orders of the last 24 hours and the time of the reading. The dashboard
 * gives it ->refresh(15), so the number re-reads itself every 15 seconds.
 */
final class LiveOrdersWidget extends Widget
{
    public function widgetType(): string
    {
        return 'stats';
    }

    public function data(): array
    {
        return StatsOverviewWidget::make()
            ->stat('Orders, 24 h', Order::query()->where('placed_at', '>=', now()->subDay())->count(), 'blue', 'shopping-cart')
            ->stat('Read at', now()->format('H:i:s'), 'gray', 'clock')
            ->data();
    }
}

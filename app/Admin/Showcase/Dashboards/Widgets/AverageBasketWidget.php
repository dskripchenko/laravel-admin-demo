<?php

namespace App\Admin\Showcase\Dashboards\Widgets;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\StatsOverviewWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/**
 * A custom widget: a class of its own with a slug of its own, drawn by a
 * built-in type ('stats'). data() reads the dashboard's period through
 * dashboardContext(), which also makes the period switcher appear.
 */
final class AverageBasketWidget extends Widget
{
    public function widgetType(): string
    {
        return 'stats';
    }

    public function data(): array
    {
        $context = $this->dashboardContext();
        $orders = $context->constrain(Order::query(), 'placed_at')
            ->whereIn('status', OrderStatus::revenue());

        $count = (clone $orders)->count();
        $average = $count > 0 ? (float) (clone $orders)->avg('total') : 0.0;
        $items = $count > 0 ? (clone $orders)->withCount('items')->get()->avg('items_count') : 0;

        $period = $context->isAll() ? __('all time') : __(':days days', ['days' => $context->days()]);

        return StatsOverviewWidget::make()
            ->stat('Period', $period, 'gray', 'calendar')
            ->stat('Average basket', '$'.number_format($average, 2), 'amber', 'receipt')
            ->stat('Items per order', number_format((float) $items, 1), 'blue', 'package')
            ->stat('Paid orders', $count, 'green', 'check-circle')
            ->data();
    }
}

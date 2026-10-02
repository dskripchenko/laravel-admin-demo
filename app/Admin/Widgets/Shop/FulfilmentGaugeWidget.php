<?php

namespace App\Admin\Widgets\Shop;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\GaugeWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/** Share of closed orders that were delivered (not cancelled or refunded). */
final class FulfilmentGaugeWidget extends Widget
{
    public function widgetType(): string
    {
        return 'gauge';
    }

    public function data(): array
    {
        $closed = $this->dashboardContext()->constrain(Order::query(), 'placed_at')
            ->whereIn('status', [OrderStatus::Delivered->value, OrderStatus::Cancelled->value, OrderStatus::Refunded->value]);
        $total = (clone $closed)->count();
        $delivered = (clone $closed)->where('status', OrderStatus::Delivered->value)->count();

        return GaugeWidget::make()
            ->value($total > 0 ? round($delivered / $total * 100, 1) : 0)
            ->range(0, 100)
            ->unit('%')
            ->threshold(0, 80, 'red')
            ->threshold(80, 90, 'amber')
            ->threshold(90, 100, 'green')
            ->data();
    }
}

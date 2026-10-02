<?php

namespace App\Admin\Widgets\Shop;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/** Orders of the selected period by status. */
final class OrderStatusWidget extends Widget
{
    public function widgetType(): string
    {
        return 'chart';
    }

    public function data(): array
    {
        $counts = $this->dashboardContext()
            ->constrain(Order::query(), 'placed_at')
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $labels = [];
        $values = [];
        foreach (OrderStatus::cases() as $status) {
            $labels[] = $status->label();
            $values[] = (int) ($counts[$status->value] ?? 0);
        }

        return ChartWidget::make()
            ->chartType('doughnut')
            ->labels($labels)
            ->dataset('Orders', $values)
            ->data();
    }
}

<?php

namespace App\Admin\Showcase\Dashboards\Widgets;

use App\Admin\Resources\Shop\OrderResource;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/** Orders by payment method over the selected period: a doughnut. */
final class PaymentMethodsWidget extends Widget
{
    public function widgetType(): string
    {
        return 'chart';
    }

    public function data(): array
    {
        $counts = $this->dashboardContext()->constrain(Order::query(), 'placed_at')
            ->selectRaw('payment_method, count(*) as aggregate')
            ->groupBy('payment_method')
            ->pluck('aggregate', 'payment_method');

        $labels = [];
        $values = [];
        foreach (OrderResource::PAYMENT_METHODS as $method => $label) {
            $labels[] = __($label);
            $values[] = (int) ($counts[$method] ?? 0);
        }

        return ChartWidget::make()->chartType('doughnut')->labels($labels)->dataset(__('Orders'), $values)->data();
    }
}

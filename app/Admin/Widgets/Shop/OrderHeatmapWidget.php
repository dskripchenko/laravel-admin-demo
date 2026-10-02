<?php

namespace App\Admin\Widgets\Shop;

use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\HeatmapWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/** When people order: weekday × three-hour slot, over the selected period. */
final class OrderHeatmapWidget extends Widget
{
    public function widgetType(): string
    {
        return 'heatmap';
    }

    public function data(): array
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
        $slots = ['06–09', '09–12', '12–15', '15–18', '18–21', '21–24'];
        $matrix = array_fill(0, 7, array_fill(0, 6, 0));

        $this->dashboardContext()->constrain(Order::query(), 'placed_at')
            ->get(['placed_at'])
            ->each(function (Order $order) use (&$matrix): void {
                $slot = intdiv(max(6, min(23, $order->placed_at->hour)) - 6, 3);
                $matrix[$order->placed_at->dayOfWeekIso - 1][$slot]++;
            });

        return HeatmapWidget::make()->axes($days, $slots)->matrix($matrix)->data();
    }
}

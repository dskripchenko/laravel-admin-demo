<?php

namespace App\Admin\Widgets\Shop;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;
use Illuminate\Support\Carbon;

/**
 * Revenue per month over the last twelve months. Grouped in
 * PHP, so the same code runs on SQLite, MySQL and PostgreSQL.
 */
final class RevenueChartWidget extends Widget
{
    public function widgetType(): string
    {
        return 'chart';
    }

    public function data(): array
    {
        $months = collect(range(11, 0))->map(fn (int $i) => Carbon::now()->startOfMonth()->subMonths($i));
        $revenue = array_fill_keys($months->map->format('Y-m')->all(), 0.0);

        Order::query()
            ->where('placed_at', '>=', $months->first())
            ->get(['placed_at', 'total', 'status'])
            ->each(function (Order $order) use (&$revenue): void {
                $key = $order->placed_at->format('Y-m');
                if (in_array($order->status->value, OrderStatus::revenue(), true)) {
                    $revenue[$key] += (float) $order->total;
                }
            });

        return ChartWidget::make()
            ->chartType('bar')
            ->labels($months->map->format('M')->all())
            ->dataset('Revenue, $', array_map(fn (float $v) => round($v), array_values($revenue)), '#6366f1')
            ->data();
    }
}

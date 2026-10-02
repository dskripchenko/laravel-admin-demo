<?php

namespace App\Admin\Widgets\Shop;

use App\Enums\OrderStatus;
use App\Models\Shop\Customer;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Widget\StatsOverviewWidget;
use Dskripchenko\LaravelAdmin\Widget\Widget;

/**
 * Revenue, orders, average order and new customers over the selected period,
 * each compared with the period before it.
 */
final class SalesKpiWidget extends Widget
{
    public function widgetType(): string
    {
        return 'stats';
    }

    public function data(): array
    {
        $context = $this->dashboardContext();
        $days = $context->days() ?? 365;
        $from = now()->subDays($days);
        $previousFrom = now()->subDays($days * 2);

        $current = $this->totals($from, now());
        $previous = $this->totals($previousFrom, $from);

        $stats = StatsOverviewWidget::make()
            ->stat('Revenue', round($current['revenue']), 'green', 'dollar-sign')->money('USD')
            ->trend(...$this->delta($current['revenue'], $previous['revenue']))
            ->stat('Orders', $current['orders'], 'blue', 'shopping-cart')
            ->trend(...$this->delta($current['orders'], $previous['orders']))
            ->stat('Average order', round($current['average'], 2), 'amber', 'receipt')->money('USD', 2)
            ->trend(...$this->delta($current['average'], $previous['average']))
            ->stat('New customers', $current['customers'], 'gray', 'user-plus')
            ->trend(...$this->delta($current['customers'], $previous['customers']));

        return $stats->data();
    }

    /** @return array{revenue: float, orders: int, average: float, customers: int} */
    private function totals(\DateTimeInterface $from, \DateTimeInterface $to): array
    {
        $orders = Order::query()->whereBetween('placed_at', [$from, $to]);
        $revenue = (float) (clone $orders)->whereIn('status', OrderStatus::revenue())->sum('total');
        $count = (clone $orders)->count();

        return [
            'revenue' => $revenue,
            'orders' => $count,
            'average' => $count > 0 ? (float) (clone $orders)->avg('total') : 0.0,
            'customers' => Customer::query()->whereBetween('created_at', [$from, $to])->count(),
        ];
    }

    /** @return array{float, string} */
    private function delta(float $current, float $previous): array
    {
        if ($previous <= 0) {
            return [0.0, 'up'];
        }
        $change = round(($current - $previous) / $previous * 100, 1);

        return [abs($change), $change >= 0 ? 'up' : 'down'];
    }
}

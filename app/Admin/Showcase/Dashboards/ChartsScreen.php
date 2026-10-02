<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\ShowcaseScreen;
use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Widget\ChartWidget;
use Illuminate\Support\Carbon;

/**
 * Dashboards › Charts: one ChartWidget class draws every kind of chart —
 * line, bar, stacked bar, area, radar, pie and doughnut. The backend sends
 * labels and datasets; the panel picks the drawing. Several instances of one
 * widget class sit side by side, each named with ->withSlug().
 */
final class ChartsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-dashboards-charts';
    }

    public static function group(): string
    {
        return 'dashboards';
    }

    public static function icon(): string
    {
        return 'chart-line';
    }

    public function name(): string
    {
        return 'Charts';
    }

    public function description(): ?string
    {
        return 'ChartWidget: line, bar, stacked bar, area, radar, pie and doughnut.';
    }

    protected function demo(): array
    {
        [$months, $byStatus] = $this->ordersByMonth();
        $statuses = array_map(fn (OrderStatus $s) => __($s->label()), OrderStatus::cases());
        $totals = array_map(fn (OrderStatus $s) => array_sum($byStatus[$s->value]), OrderStatus::cases());

        return [
            Layout::dashboard([
                ChartWidget::make()->withSlug('orders-line')->title('Line: orders per month')->size(6)->rowSpan(2)
                    ->chartType('line')->labels($months)
                    ->dataset(__('Delivered'), $byStatus['delivered'], '#10b981')
                    ->dataset(__('Cancelled'), $byStatus['cancelled'], '#ef4444'),

                ChartWidget::make()->withSlug('paid-shipped-bar')->title('Bar: paid and shipped')->size(6)->rowSpan(2)
                    ->chartType('bar')->labels($months)
                    ->dataset(__('Paid'), $byStatus['paid'], '#6366f1')
                    ->dataset(__('Shipped'), $byStatus['shipped'], '#0ea5e9'),

                ChartWidget::make()->withSlug('status-stacked')->title('Stacked bar: every status')->size(6)->rowSpan(2)
                    ->chartType('bar')->stacked()->labels($months)
                    ->dataset(__('Delivered'), $byStatus['delivered'], '#10b981')
                    ->dataset(__('Shipped'), $byStatus['shipped'], '#0ea5e9')
                    ->dataset(__('Paid'), $byStatus['paid'], '#6366f1')
                    ->dataset(__('Pending'), $byStatus['pending'], '#f59e0b'),

                ChartWidget::make()->withSlug('delivered-area')->title('Area: delivered orders')->size(6)->rowSpan(2)
                    ->chartType('area')->labels($months)
                    ->dataset(__('Delivered'), $byStatus['delivered'], '#10b981'),

                ChartWidget::make()->withSlug('status-radar')->title('Radar: orders by status')->size(4)->rowSpan(3)
                    ->chartType('radar')->labels($statuses)
                    ->dataset(__('Orders'), $totals, '#6366f1'),

                ChartWidget::make()->withSlug('status-pie')->title('Pie: orders by status')->size(4)->rowSpan(3)
                    ->chartType('pie')->labels($statuses)
                    ->dataset(__('Orders'), $totals),

                ChartWidget::make()->withSlug('payment-doughnut')->title('Doughnut: payment methods')->size(4)->rowSpan(3)
                    ->chartType('doughnut')->labels([__('Card'), __('PayPal'), __('Invoice')])
                    ->dataset(__('Orders'), [
                        Order::query()->where('payment_method', 'card')->count(),
                        Order::query()->where('payment_method', 'paypal')->count(),
                        Order::query()->where('payment_method', 'invoice')->count(),
                    ]),
            ]),
        ];
    }

    /**
     * Month labels of the last six full months and, per status, the number of
     * orders placed in each of them.
     *
     * @return array{list<string>, array<string, list<int>>}
     */
    private function ordersByMonth(): array
    {
        $months = collect(range(6, 1))->map(fn (int $i) => Carbon::now()->startOfMonth()->subMonths($i));
        $keys = $months->map->format('Y-m')->all();
        $byStatus = [];
        foreach (OrderStatus::cases() as $status) {
            $byStatus[$status->value] = array_fill_keys($keys, 0);
        }

        Order::query()->whereBetween('placed_at', [$months->first(), Carbon::now()->startOfMonth()])->get(['placed_at', 'status'])
            ->each(function (Order $order) use (&$byStatus): void {
                $byStatus[$order->status->value][$order->placed_at->format('Y-m')]++;
            });

        return [
            $months->map(fn (Carbon $month) => $month->translatedFormat('M'))->all(),
            array_map(array_values(...), $byStatus),
        ];
    }
}

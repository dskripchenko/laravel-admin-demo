<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\Dashboards\Widgets\AverageBasketWidget;
use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Widget\DashboardContext;

/**
 * Dashboards › Custom widgets: a Widget subclass computes its data from the
 * dashboard's period. On a dashboard screen the period comes from the
 * switcher; anywhere else the widget can be handed a context explicitly.
 */
final class CustomWidgetScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-dashboards-custom';
    }

    public static function group(): string
    {
        return 'dashboards';
    }

    public static function icon(): string
    {
        return 'puzzle';
    }

    public function name(): string
    {
        return 'Custom widgets and periods';
    }

    public function description(): ?string
    {
        return 'A Widget subclass that reads the period from dashboardContext().';
    }

    protected function demo(): array
    {
        return [
            Layout::dashboard([
                AverageBasketWidget::make()->withSlug('basket-7d')->title('Last 7 days')->size(12)
                    ->withDashboardContext(new DashboardContext('7d')),
                AverageBasketWidget::make()->withSlug('basket-90d')->title('Last 90 days')->size(12)
                    ->withDashboardContext(new DashboardContext('90d')),
                AverageBasketWidget::make()->withSlug('basket-all')->title('All time')->size(12)
                    ->withDashboardContext(new DashboardContext('all')),
            ]),
            Layout::markdown(__('The same class three times, each with its own period. On a dashboard screen you do not pass the context: the period switcher does, and it appears by itself once a widget reads dashboardContext().'))->card(),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }

    protected function sourceClasses(): array
    {
        return [AverageBasketWidget::class];
    }
}

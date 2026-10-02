<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\Dashboards\Widgets\CustomerSegmentsWidget;
use App\Admin\Showcase\Dashboards\Widgets\LiveOrdersWidget;
use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Dashboards › Dashboard screens: what a DashboardScreen adds on top of a
 * grid of widgets — the period switcher, polling, per-widget permissions and
 * a layout every user can rearrange and keep.
 */
final class DashboardScreenScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-dashboards-screen';
    }

    public static function group(): string
    {
        return 'dashboards';
    }

    public static function icon(): string
    {
        return 'layout-grid';
    }

    public function name(): string
    {
        return 'Dashboard screens';
    }

    public function description(): ?string
    {
        return 'Periods, polling, permission-gated widgets and per-user layouts.';
    }

    public function commandBar(): array
    {
        return [
            Link::make('Open the dashboard')->href($this->dashboardUrl())->icon('layout-dashboard')->primary(),
        ];
    }

    protected function demo(): array
    {
        $rows = [
            ['Period switcher', 'periods(), defaultPeriod()', 'The toolbar offers 7 / 30 / 90 days and all time; every widget gets the period in dashboardContext().'],
            ['Polling', '->refresh(15)', 'The live widget re-reads its data every 15 seconds; one timer serves the whole dashboard.'],
            ['Permissions', "->permission('admin.customers.view')", 'Sign in as Editor: the segments widget is gone, and its query never runs.'],
            ['Per-user layout', 'Edit → drag, resize, hide, add', 'Saved for your account in admin_dashboard_layouts, on top of the layout in the code.'],
            ['Widget slugs', "->withSlug('orders-by-weekday')", 'Two charts of one class keep their place in your saved layout: each has a name of its own.'],
        ];

        $table = '| '.__('Feature').' | '.__('In the code').' | '.__('What to try').' |'."\n|---|---|---|\n";
        foreach ($rows as [$feature, $code, $try]) {
            $table .= '| **'.__($feature).'** | `'.$code.'` | '.__($try)." |\n";
        }

        return [
            Layout::block('A dashboard of its own', [
                Layout::markdown(__('A class extending DashboardScreen lists its widgets and is registered like any screen; the menu links to it with MenuNode::dashboard().')."\n\n".$table
                    ."\n\n".'['.__('Open the showcase dashboard').']('.$this->dashboardUrl().')'),
            ])->icon('layout-dashboard'),
        ];
    }

    protected function sourceClasses(): array
    {
        return [ShowcaseDashboard::class, LiveOrdersWidget::class, CustomerSegmentsWidget::class];
    }

    private function dashboardUrl(): string
    {
        return '/'.trim((string) config('admin.path', 'admin'), '/').'/dashboard/'.ShowcaseDashboard::slug();
    }
}

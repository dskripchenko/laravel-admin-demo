<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\ShowcaseGroup;
use Dskripchenko\LaravelAdmin\Facades\Admin;
use Dskripchenko\LaravelAdmin\Menu\MenuNode;

final class DashboardsGroup extends ShowcaseGroup
{
    public static function key(): string
    {
        return 'dashboards';
    }

    public static function title(): string
    {
        return 'Dashboards';
    }

    public static function about(): string
    {
        return 'Widgets of every kind, periods, per-user layouts.';
    }

    public static function icon(): string
    {
        return 'layout-dashboard';
    }

    public static function screens(): array
    {
        return [
            KpiScreen::class,
            ChartsScreen::class,
            ListsScreen::class,
            CustomWidgetScreen::class,
            DashboardScreenScreen::class,
        ];
    }

    public static function boot(): void
    {
        Admin::screen([ShowcaseDashboard::class]);
    }

    public static function menu(): array
    {
        return [
            ...parent::menu(),
            MenuNode::dashboard(ShowcaseDashboard::slug())->label('Live dashboard')->icon('layout-dashboard'),
        ];
    }
}

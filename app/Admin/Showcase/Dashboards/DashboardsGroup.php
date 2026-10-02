<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Showcase\ShowcaseGroup;

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
        return [];
    }
}

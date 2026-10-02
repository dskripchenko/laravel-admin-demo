<?php

namespace App\Admin\Showcase\Navigation;

use App\Admin\Showcase\ShowcaseGroup;

final class NavigationGroup extends ShowcaseGroup
{
    public static function key(): string
    {
        return 'navigation';
    }

    public static function title(): string
    {
        return 'Navigation';
    }

    public static function about(): string
    {
        return 'Nested menus, badges, links between screens and records.';
    }

    public static function icon(): string
    {
        return 'map';
    }

    public static function screens(): array
    {
        return [];
    }
}

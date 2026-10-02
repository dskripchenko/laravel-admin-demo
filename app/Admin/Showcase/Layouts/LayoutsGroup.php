<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseGroup;

final class LayoutsGroup extends ShowcaseGroup
{
    public static function key(): string
    {
        return 'layouts';
    }

    public static function title(): string
    {
        return 'Layouts';
    }

    public static function about(): string
    {
        return 'Rows, columns, tabs, accordions, modals, wizards, markdown.';
    }

    public static function icon(): string
    {
        return 'columns';
    }

    public static function screens(): array
    {
        return [
            TabsScreen::class,
            ColumnsScreen::class,
        ];
    }
}

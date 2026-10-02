<?php

namespace App\Admin\Showcase\Grids;

use App\Admin\Showcase\ShowcaseGroup;

final class GridsGroup extends ShowcaseGroup
{
    public static function key(): string
    {
        return 'grids';
    }

    public static function title(): string
    {
        return 'Grids';
    }

    public static function about(): string
    {
        return 'Columns, filters, row and bulk actions, inline editing, trees.';
    }

    public static function icon(): string
    {
        return 'table';
    }

    public static function screens(): array
    {
        return [
            GridColumnsScreen::class,
            GridFiltersScreen::class,
            GridActionsScreen::class,
            GridInlineScreen::class,
            GridTreeScreen::class,
            GridEmbeddedScreen::class,
            GridLifecycleScreen::class,
        ];
    }

    public static function resources(): array
    {
        return [
            GridColumnsResource::class,
            GridFiltersResource::class,
            GridActionsResource::class,
            GridInlineResource::class,
            GridTreeResource::class,
            GridEmbedAuthorsResource::class,
            GridEmbedPostsResource::class,
            GridReorderResource::class,
            GridTrashResource::class,
        ];
    }
}

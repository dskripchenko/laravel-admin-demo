<?php

namespace App\Admin\Showcase\Grids;

use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Grids › Tree view.
 */
final class GridTreeScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-tree';
    }

    public static function icon(): string
    {
        return 'folder-tree';
    }

    protected static function resource(): string
    {
        return GridTreeResource::class;
    }

    public function name(): string
    {
        return 'Tree view';
    }

    public function description(): ?string
    {
        return 'A self-referencing model shown as a tree, with per-node actions.';
    }

    protected function demo(): array
    {
        return [
            Layout::block('What to try', [
                Layout::markdown(__('Open the tree, expand the branches and search: the matching nodes stay visible together with their ancestors. Select a node — its toolbar has **Add a subcategory**, declared in `treeNodeActions()`, which opens the create form with the parent already picked. A double click opens the edit form.')),
            ]),
        ];
    }
}

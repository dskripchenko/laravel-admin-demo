<?php

namespace App\Admin\Showcase\Grids;

use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Grids › Reorder, replicate, soft deletes and polling: switches of a
 * resource, one method each.
 */
final class GridLifecycleScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-lifecycle';
    }

    public static function icon(): string
    {
        return 'refresh-cw';
    }

    protected static function resource(): string
    {
        return GridReorderResource::class;
    }

    public function name(): string
    {
        return 'Reorder and trash';
    }

    public function description(): ?string
    {
        return 'Drag-and-drop order, duplicating records, soft deletes with restore, auto-refresh.';
    }

    public function commandBar(): array
    {
        return [
            ...parent::commandBar(),
            Link::make('Open the trash')
                ->href('/'.trim((string) config('admin.path', 'admin'), '/').'/r/'.GridTrashResource::slug())
                ->icon('trash-2')
                ->permission(GridTrashResource::permission().'.view'),
        ];
    }

    protected function sourceClasses(): array
    {
        return [GridReorderResource::class, GridTrashResource::class];
    }

    protected function demo(): array
    {
        $table = '| '.__('Method').' | '.__('What the table gets').' |'."\n|---|---|\n"
            .'| `reorderable()`, `reorderColumn()` | '.__('A drag handle; the new order is saved to the column')." |\n"
            .'| `replicable()`, `replicate()` | '.__('A duplicate action; the hook keeps unique columns unique')." |\n"
            .'| `SoftDeletes` '.__('on the model').' | '.__('A trashed filter, restore and delete forever')." |\n"
            .'| `polling()` | '.__('The rows refresh every N seconds')." |\n";

        return [
            Layout::block('What to try', [
                Layout::markdown(__('Drag a row of the blog categories below by its handle, then duplicate one. **Open the trash** at the top — a posts table of its own page: delete a post, switch the trashed filter to see it, restore it or delete it forever. That table also refreshes itself every 30 seconds.')),
            ]),
            // The live table itself: the resource's index, embedded into the screen.
            Layout::resourceIndex(GridReorderResource::class),
            Layout::block('One method each', [Layout::markdown($table)]),
        ];
    }
}

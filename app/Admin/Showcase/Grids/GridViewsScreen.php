<?php

namespace App\Admin\Showcase\Grids;

use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Grids › Kanban and calendar: Resource::kanban() and Resource::calendar()
 * add two views of the same records next to the list.
 */
final class GridViewsScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-views';
    }

    public static function icon(): string
    {
        return 'layout-grid';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    protected static function resource(): string
    {
        return GridViewsResource::class;
    }

    public function name(): string
    {
        return 'Kanban and calendar views';
    }

    public function description(): ?string
    {
        return 'The same posts as a list, a kanban board by status and a calendar by date.';
    }

    protected function demo(): array
    {
        $table = '| '.__('Method').' | '.__('What the page gets').' |'."\n|---|---|\n"
            .'| `kanban()` | '.__('A column per status; a dragged card changes the status, with the update permission and editableForRow()')." |\n"
            .'| `calendar()` | '.__('Events by a date column; a dragged event moves the date, a click on an empty day creates a record')." |\n"
            .'| `initialView()` '.__('on').' `Layout::resourceIndex()` | '.__('The view the embedded list opens on')." |\n";

        return [
            Layout::block('What to try', [
                Layout::markdown(__('The list below opens on its kanban board: drag a post from Draft to In review, or switch to the calendar and move a post to another day. The shop\'s orders have the same views, with the moves limited to the order flow: open **Orders** and pick Kanban.')),
            ]),
            // The live list itself, opened on its kanban view.
            Layout::resourceIndex(GridViewsResource::class)->initialView('kanban'),
            Layout::block('One method each', [Layout::markdown($table)]),
        ];
    }
}

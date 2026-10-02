<?php

namespace App\Admin\Showcase\Grids;

use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Grids › Inline editing.
 */
final class GridInlineScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-inline';
    }

    public static function icon(): string
    {
        return 'pencil';
    }

    protected static function resource(): string
    {
        return GridInlineResource::class;
    }

    public function name(): string
    {
        return 'Inline editing';
    }

    public function description(): ?string
    {
        return 'Text, number, select, date, switch and textarea cells, validated on the server.';
    }

    protected function demo(): array
    {
        $types = '| '.__('Column').' | '.__('Input').' | '.__('Rules').' |'."\n|---|---|---|\n"
            ."| title | text | required, min:3, max:160 |\n"
            ."| status | select | in:draft,review,published |\n"
            ."| views | number | integer, min:0 |\n"
            ."| published_at | date | date |\n"
            ."| is_featured | switcher | boolean |\n"
            ."| excerpt | textarea | max:500 |\n";

        return [
            Layout::block('What to try', [
                Layout::markdown(__('Click a cell of the table to edit it in place; Enter or leaving the cell saves, Escape cancels. Clear a title or type a negative number of views to see the server refuse it. The title of a published post is locked by `editableForRow()`. Viewers get the same table, read-only.')),
            ]),
            Layout::block('Editable columns', [Layout::markdown($types)]),
        ];
    }
}

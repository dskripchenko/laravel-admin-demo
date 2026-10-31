<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use App\Support\DemoBoard;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdminBoard\Screens\BoardScreen;
use Dskripchenko\LaravelAdminBoard\Support\BoardLayout;

/**
 * Layouts › Collaborative board: a whiteboard several people edit at once,
 * from the sister package dskripchenko/laravel-admin-board. The server puts
 * every change in order, so everybody ends up with the same board; the
 * others' cursors and selections show live. Without laravel-realtime, as
 * here, the board polls for the others' changes.
 */
final class CollaborativeBoardScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-board';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'palette';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Collaborative board');
    }

    public function description(): ?string
    {
        return __('A whiteboard edited by several people at once: stickers, shapes, frames and connectors, with live cursors.');
    }

    protected function demo(): array
    {
        $board = DemoBoard::find();

        return [
            Layout::block(__('What to try'), [
                Layout::markdown(__('Add a sticker, draw a connector between two of them, drop things into a frame. Then open this page in a private window, sign in as another demo account and watch each other\'s cursors and changes. Editors and administrators draw (`admin.boards.edit`); a viewer gets the board read-only. Every board resets with the stand, once an hour.')),
            ]),
            // The board's API, its user and its access come from the package.
            BoardLayout::for($board, title: '', height: 600),
            Layout::block(__('How it fits together'), [
                Layout::markdown(implode("\n", [
                    '| '.__('Part').' | '.__('What it does').' |',
                    '|---|---|',
                    '| `Layout::board()` | '.__('The core draws the editor and talks to the board\'s API through the panel\'s client').' |',
                    '| `BoardLayout::for($board)` | '.__('The package fills in the endpoint, the user, read-only and the polling interval').' |',
                    '| `/boards/{id}/snapshot`, `batches`, `poll`, `presence` | '.__('The package stores the board, puts the changes in order and reports who is there').' |',
                    '| `HasBoard` | '.__('A board per record: $project->boardLayout() on its screens').' |',
                ])),
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make(__('All boards'))
                ->href('/'.trim((string) config('admin.path', 'admin'), '/').'/r/boards')
                ->icon('list')
                ->permission('admin.boards.view'),
            Link::make(__('Open full screen'))
                ->href(BoardScreen::url(DemoBoard::find()->getKey()))
                ->icon('external-link')
                ->permission('admin.boards.view'),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

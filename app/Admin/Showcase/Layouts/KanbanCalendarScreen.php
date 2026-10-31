<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Resource\ActionFailedException;

/**
 * Layouts › Kanban and calendar: a board and a calendar written as markup.
 * Every change — a card moved, an event dragged, a line typed on the source
 * tab — calls a screen method with the whole new markup, and the lock button
 * calls another one. Here the markup is kept in your session, so your edits
 * stay yours and go away with the hourly reset; a real screen would save it
 * to a model.
 */
final class KanbanCalendarScreen extends ShowcaseScreen
{
    private const KEY = 'showcase.kanban-calendar.';

    /** The longest markup the methods accept. */
    private const MAX_LENGTH = 20_000;

    public static function slug(): string
    {
        return 'showcase-layouts-kanban-calendar';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'layout-grid';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Kanban and calendar');
    }

    public function description(): ?string
    {
        return __('Layout::kanban() and Layout::calendar() from markup, saved through screen methods, with a lock.');
    }

    public function query(mixed ...$params): array
    {
        return [
            'board' => session(self::KEY.'board', DocumentSamples::kanban()),
            'board_locked' => (bool) session(self::KEY.'board_locked', false),
            'schedule' => session(self::KEY.'schedule', DocumentSamples::calendar()),
            'schedule_locked' => (bool) session(self::KEY.'schedule_locked', false),
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block(__('What to try'), [
                Layout::markdown(__('Drag a card to another column, press **+** in a column header, open a card to edit it, or change the markup on the **Source** tab. Drag an event in the calendar or click an empty day. Each change is saved for your session; the lock button makes the board read-only. The methods need `admin.posts.update`, so a viewer gets both read-only.')),
            ]),

            Layout::kanban('board')
                ->title(__('A kanban board'))
                ->editable('saveBoard', permission: 'admin.posts.update')
                ->lockable('lockBoard', permission: 'admin.posts.update')
                ->locked('board_locked'),

            Layout::calendar('schedule')
                ->title(__('An event calendar'))
                ->initialView('month')
                // The day of the first sample event, so its month is shown.
                ->date(now()->addDay()->toDateString())
                ->views(['month', 'week', 'agenda'])
                ->editable('saveSchedule', permission: 'admin.posts.update')
                ->lockable('lockSchedule', permission: 'admin.posts.update')
                ->locked('schedule_locked'),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make(__('Start over'))->method('startOver')->icon('rotate-ccw')
                ->confirm(__('Bring back the sample board and calendar?')),
        ];
    }

    /** The board after a change: refused while it is locked. */
    public function saveBoard(string $markup): array
    {
        return $this->save('board', $markup);
    }

    public function lockBoard(bool $locked): array
    {
        session([self::KEY.'board_locked' => $locked]);

        return ['message' => $locked ? __('The board is locked.') : __('The board is unlocked.')];
    }

    public function saveSchedule(string $markup): array
    {
        return $this->save('schedule', $markup);
    }

    public function lockSchedule(bool $locked): array
    {
        session([self::KEY.'schedule_locked' => $locked]);

        return ['message' => $locked ? __('The calendar is locked.') : __('The calendar is unlocked.')];
    }

    public function startOver(): array
    {
        session()->forget(array_map(fn (string $key) => self::KEY.$key, ['board', 'board_locked', 'schedule', 'schedule_locked']));

        return ['message' => __('The samples are back.'), 'refresh' => true];
    }

    private function save(string $key, string $markup): array
    {
        if (session(self::KEY.$key.'_locked', false)) {
            // Throwing rolls the change back in the browser, with this toast.
            throw new ActionFailedException(__('It is locked: unlock it first.'));
        }
        if (mb_strlen($markup) > self::MAX_LENGTH) {
            throw new ActionFailedException(__('The markup is longer than :max characters.', ['max' => self::MAX_LENGTH]));
        }
        session([self::KEY.$key => $markup]);

        return [];
    }
}

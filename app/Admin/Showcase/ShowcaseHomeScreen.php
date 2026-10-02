<?php

namespace App\Admin\Showcase;

use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Screen\Screen;

/**
 * The showcase's front page: what each group demonstrates and where to look.
 */
final class ShowcaseHomeScreen extends Screen
{
    /** Group key => [title, what it shows]. */
    public const GROUPS = [
        'dashboards' => ['Dashboards', 'Widgets, periods, per-user layouts.'],
        'forms' => ['Forms', 'Every field type, validation, reactive listeners.'],
        'grids' => ['Grids', 'Sorting, filters, inline editing, bulk actions, saved views.'],
        'layouts' => ['Layouts', 'Rows, columns, tabs, blocks, modals, wizards.'],
        'actions' => ['Actions', 'Buttons, confirmations, modal forms, downloads.'],
        'navigation' => ['Navigation', 'Trees, nested menus, links between records.'],
        'notifications' => ['Notifications', 'Toasts, alerts, the notification centre.'],
    ];

    public static function slug(): string
    {
        return 'showcase';
    }

    public function name(): string
    {
        return __('Showcase');
    }

    public function description(): ?string
    {
        return __('Every page shows a working example and the PHP that built it.');
    }

    public function query(mixed ...$params): array
    {
        return [];
    }

    public function layout(): array
    {
        $markdown = __('Each example is an ordinary screen class of this application. Under the example you will find its source, read through reflection from the very class that rendered the page.')."\n\n";
        $markdown .= '| '.__('Group').' | '.__('What it shows').' |'."\n|---|---|\n";
        foreach (self::GROUPS as [$title, $about]) {
            $markdown .= '| **'.__($title).'** | '.__($about)." |\n";
        }

        return [Layout::markdown($markdown)->card()];
    }
}

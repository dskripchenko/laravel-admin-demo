<?php

namespace App\Admin\Showcase\Notifications;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Infolist\BadgeEntry;
use Dskripchenko\LaravelAdmin\Infolist\IconEntry;
use Dskripchenko\LaravelAdmin\Infolist\TextEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Notifications › Callouts and statuses: messages that stay on the page —
 * Markdown callouts, and status badges and icons in an infolist.
 */
final class CalloutsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-notifications-callouts';
    }

    public static function group(): string
    {
        return 'notifications';
    }

    public static function icon(): string
    {
        return 'alert-circle';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Callouts';
    }

    public function description(): ?string
    {
        return 'Markdown callouts on a page, status badges and icons in an infolist.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'sync' => 'ok',
            'queue' => 'degraded',
            'backup' => 'failed',
            'mail' => 'paused',
            'https' => true,
            'debug' => false,
            'checked_at' => now()->subMinutes(3)->toDateTimeString(),
        ];
    }

    protected function demo(): array
    {
        $statuses = ['ok' => 'success', 'degraded' => 'warning', 'failed' => 'danger', 'paused' => 'neutral'];
        $labels = ['ok' => 'OK', 'degraded' => 'Degraded', 'failed' => 'Failed', 'paused' => 'Paused'];

        return [
            Layout::columns([
                Layout::block('Callouts', [
                    Layout::markdown(__(<<<'MD'
> **Note** Orders placed before 2024 are archived and read-only.

> **Tip** Press `Ctrl K` to search everywhere.

> **Important** The price list changes on the first of the month.

> **Warning** Deleting a category leaves its products without one.

> **Caution** Refunds cannot be undone.

> [!NOTE]
> GitHub's form works too: `> [!NOTE]`, `> [!WARNING]` and the rest.
MD)),
                ])->icon('file-text')->description('A blockquote that starts with Note, Tip, Important, Warning or Caution in bold.'),
                Layout::block('Statuses', [
                    Layout::infolist([
                        BadgeEntry::make('sync')->label('Catalog sync')->colors($statuses)->labels($labels),
                        BadgeEntry::make('queue')->label('Queue')->colors($statuses)->labels($labels),
                        BadgeEntry::make('backup')->label('Nightly backup')->colors($statuses)->labels($labels),
                        BadgeEntry::make('mail')->label('Newsletter')->colors($statuses)->labels($labels),
                        IconEntry::make('https')->label('HTTPS')->trueIcon('shield-check')->falseIcon('x-circle')
                            ->trueLabel('On')->falseLabel('Off'),
                        IconEntry::make('debug')->label('Debug mode')->trueIcon('bug')->falseIcon('check-circle')
                            ->trueLabel('On')->falseLabel('Off'),
                        TextEntry::make('checked_at')->label('Checked')->asDateTime(),
                    ]),
                ])->icon('activity')->description('BadgeEntry maps a value to a colour and a label; IconEntry draws a boolean.'),
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

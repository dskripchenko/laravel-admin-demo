<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Infolist\BadgeEntry;
use Dskripchenko\LaravelAdmin\Infolist\TextEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Cards and split views: titled blocks, markdown cards and an uneven
 * two-pane split — a summary on the left, the form on the right.
 */
final class CardsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-cards';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'layout';
    }

    public function name(): string
    {
        return 'Cards and splits';
    }

    public function description(): ?string
    {
        return 'Layout::block() cards side by side, markdown cards, a 1:2 split.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'number' => 'ORD-10421',
            'customer' => 'Ada Lovelace',
            'status' => 'paid',
            'total' => 129.5,
            'reply_to' => 'ada@example.com',
            'topic' => 'delivery',
            'reply' => '',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::markdown('### $12,480'."\n\n".__('Revenue this week, **+8%**'))->card(),
                Layout::markdown('### 214'."\n\n".__('Orders this week, **+3%**'))->card(),
                Layout::markdown('### 1.8%'."\n\n".__('Returns, down from 2.1%'))->card(),
            ]),

            Layout::columns([
                Layout::block('Order', [
                    Layout::infolist([
                        TextEntry::make('number')->copyable(),
                        TextEntry::make('customer'),
                        BadgeEntry::make('status')->colors(['paid' => 'success', 'pending' => 'warning'])
                            ->labels(['paid' => 'Paid', 'pending' => 'Pending']),
                        TextEntry::make('total')->asMoney('USD'),
                    ]),
                ])->icon('receipt')->description(__('The left pane: one third')),

                Layout::block('Reply to the customer', [
                    Input::make('reply_to')->title('To')->type('email'),
                    Select::make('topic')->options(['delivery' => 'Delivery', 'refund' => 'Refund', 'other' => 'Other']),
                    Textarea::make('reply')->rows(5)->placeholder('Write a reply…'),
                ])->icon('mail')->description(__('The right pane: two thirds')),
            ])->ratios([1, 2]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

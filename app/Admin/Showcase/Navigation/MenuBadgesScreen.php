<?php

namespace App\Admin\Showcase\Navigation;

use App\Admin\Showcase\ShowcaseScreen;
use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Infolist\TextEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Navigation › Badges: a number or a short word next to a menu entry —
 * here, the count of orders waiting for payment, and a "new" label on some
 * showcase pages.
 */
final class MenuBadgesScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-navigation-badges';
    }

    public static function group(): string
    {
        return 'navigation';
    }

    public static function icon(): string
    {
        return 'tag';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Menu badges';
    }

    public function description(): ?string
    {
        return 'MenuNode::badge() — counters and labels next to an entry.';
    }

    /**
     * The badge of the Orders entry in "Nested menu": pending orders.
     *
     * The menu is built on every request, so the badge must be cheap — one
     * indexed count. rescue() keeps the console working before the tables exist.
     */
    public static function pendingOrders(): ?int
    {
        return rescue(
            fn () => Order::query()->where('status', OrderStatus::Pending->value)->count() ?: null,
            null,
            false,
        );
    }

    public function query(mixed ...$params): array
    {
        return ['pending' => self::pendingOrders() ?? 0];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::markdown(__(<<<'MD'
`->badge()` takes a number or a string. Look at the sidebar:

- **Nested menu › L2 · Shop › Orders** shows how many orders wait for payment — a count from the demo data;
- this page and a few others carry a **new** label: `ShowcaseScreen::badge()` returns it and the menu node passes it on.

A badge is a value, computed when the menu is built — on every request. Keep it to one cheap query, or cache it.
MD))->card(),
                Layout::block('Right now', [
                    Layout::infolist([
                        TextEntry::make('pending')->label('Pending orders'),
                    ]),
                ])->icon('shopping-cart')->description('The same number as the badge in the sidebar.'),
            ])->ratios([2, 1]),
        ];
    }
}

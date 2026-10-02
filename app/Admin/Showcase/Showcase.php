<?php

namespace App\Admin\Showcase;

use Dskripchenko\LaravelAdmin\Resource\Resource;

/**
 * The showcase's table of contents: the groups in menu order.
 */
final class Showcase
{
    /** @var list<class-string<ShowcaseGroup>> */
    public const GROUPS = [
        Dashboards\DashboardsGroup::class,
        Forms\FormsGroup::class,
        Grids\GridsGroup::class,
        Layouts\LayoutsGroup::class,
        Actions\ActionsGroup::class,
        Navigation\NavigationGroup::class,
        Notifications\NotificationsGroup::class,
    ];

    /** @return list<class-string<ShowcaseScreen>> */
    public static function screens(): array
    {
        return array_merge(...array_map(fn (string $group) => $group::screens(), self::GROUPS));
    }

    public static function boot(): void
    {
        foreach (self::GROUPS as $group) {
            $group::boot();
        }
    }

    /** @return list<class-string<resource>> */
    public static function resources(): array
    {
        return array_merge(...array_map(fn (string $group) => $group::resources(), self::GROUPS));
    }
}

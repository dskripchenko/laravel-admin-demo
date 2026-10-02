<?php

namespace App\Admin\Showcase;

use Dskripchenko\LaravelAdmin\Menu\MenuNode;
use Dskripchenko\LaravelAdmin\Resource\Resource;

/**
 * One section of the showcase: its title, its menu icon and its examples.
 * Every group lives in its own directory next to its screens and lists them
 * in reading order.
 */
abstract class ShowcaseGroup
{
    /** The key of the group, as ShowcaseScreen::group() names it. */
    abstract public static function key(): string;

    abstract public static function title(): string;

    /** One line about what the group demonstrates. */
    abstract public static function about(): string;

    /** A lucide icon of the menu. */
    abstract public static function icon(): string;

    /**
     * The examples, in reading order.
     *
     * @return list<class-string<ShowcaseScreen>>
     */
    abstract public static function screens(): array;

    /**
     * Resources the examples need (a grid is a resource), registered with the
     * panel and left out of the menu.
     *
     * @return list<class-string<resource>>
     */
    public static function resources(): array
    {
        return [];
    }

    /**
     * Registers whatever else the examples need — a dashboard screen, an
     * allowlisted background handler. Called from the admin service provider.
     */
    public static function boot(): void {}

    /**
     * The menu entries of the group.
     *
     * @return list<MenuNode>
     */
    public static function menu(): array
    {
        return array_map(fn (string $screen) => $screen::menuNode(), static::screens());
    }

    public static function menuNode(): MenuNode
    {
        return MenuNode::make('showcase-'.static::key(), static::title())
            ->icon(static::icon())
            ->children(static::menu());
    }
}

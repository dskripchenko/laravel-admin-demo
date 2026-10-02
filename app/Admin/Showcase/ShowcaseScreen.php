<?php

namespace App\Admin\Showcase;

use Dskripchenko\LaravelAdmin\Contracts\Renderable;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Menu\MenuNode;
use Dskripchenko\LaravelAdmin\Screen\Screen;

/**
 * The base of every showcase page: the live example first, then the PHP that
 * built it. A subclass implements `demo()` (and `query()` when its form needs
 * initial values) and says which showcase group it belongs to.
 */
abstract class ShowcaseScreen extends Screen
{
    use ShowsSource;

    /** The showcase group of the menu: forms, grids, layouts, … */
    abstract public static function group(): string;

    /**
     * The live example.
     *
     * @return list<Renderable>
     */
    abstract protected function demo(): array;

    /** A lucide icon of the menu entry. */
    public static function icon(): string
    {
        return 'sparkles';
    }

    /**
     * A short status label next to the menu entry ("new"), or null.
     */
    public static function badge(): ?string
    {
        return null;
    }

    public static function menuNode(): MenuNode
    {
        return MenuNode::screen(static::slug())->icon(static::icon())->badge(static::badge());
    }

    public function query(mixed ...$params): array
    {
        return [];
    }

    /**
     * The method whose source is shown; null shows the whole class.
     */
    protected function sourceMethod(): ?string
    {
        return null;
    }

    /**
     * Other classes the example is made of — a resource, a widget, a job —
     * shown next to the screen's own source, one tab each.
     *
     * @return list<class-string>
     */
    protected function sourceClasses(): array
    {
        return [];
    }

    final public function layout(): array
    {
        $own = $this->sourceCode($this->sourceMethod());
        $classes = $this->sourceClasses();

        $source = $classes === []
            ? [$own]
            : [Layout::tabs([
                class_basename(static::class) => [$own],
                ...array_combine(
                    array_map(fn (string $class) => class_basename($class), $classes),
                    array_map(fn (string $class) => [$this->sourceCode(class: $class)], $classes),
                ),
            ])];

        return [
            ...$this->demo(),
            Layout::block(__("How it's built"), $source)
                ->description(__('The PHP of this page, read from the class that rendered it.')),
        ];
    }
}

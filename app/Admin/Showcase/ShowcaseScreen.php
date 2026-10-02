<?php

namespace App\Admin\Showcase;

use Dskripchenko\LaravelAdmin\Contracts\Renderable;
use Dskripchenko\LaravelAdmin\Layout\Layout;
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

    final public function layout(): array
    {
        return [
            ...$this->demo(),
            Layout::block(__("How it's built"), [$this->sourceCode($this->sourceMethod())])
                ->description(__('The PHP of this page, read from the class that rendered it.')),
        ];
    }
}

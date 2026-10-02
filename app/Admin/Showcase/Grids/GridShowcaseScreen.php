<?php

namespace App\Admin\Showcase\Grids;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Resource\Resource;

/**
 * A grid example: a grid is the index of a resource, so the example is a
 * focused resource of its own. The page explains it, shows its live table
 * (`Layout::resourceIndex()`), links to the table's own page and shows the
 * resource's source next to its own.
 */
abstract class GridShowcaseScreen extends ShowcaseScreen
{
    /**
     * The resource whose table the example is.
     *
     * @return class-string<resource>
     */
    abstract protected static function resource(): string;

    public static function group(): string
    {
        return 'grids';
    }

    /**
     * The caption and the icon of the link to the example's own page.
     *
     * @return array{0: string, 1: string}
     */
    protected static function openLink(): array
    {
        return ['Open the table', 'table'];
    }

    public function commandBar(): array
    {
        $resource = static::resource();
        [$label, $icon] = static::openLink();

        return [
            Link::make($label)
                ->href('/'.trim((string) config('admin.path', 'admin'), '/').'/r/'.$resource::slug())
                ->icon($icon)
                ->permission($resource::permission().'.view'),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }

    protected function sourceClasses(): array
    {
        return [static::resource()];
    }
}

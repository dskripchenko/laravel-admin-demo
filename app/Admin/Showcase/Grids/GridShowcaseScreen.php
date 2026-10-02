<?php

namespace App\Admin\Showcase\Grids;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Resource\Resource;

/**
 * A grid example: a grid is the index of a resource, so the example is a
 * focused resource of its own, and this page explains it, links to the live
 * table and shows the resource's source next to its own.
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

    public function commandBar(): array
    {
        $resource = static::resource();

        return [
            Link::make('Open the table')
                ->href('/'.trim((string) config('admin.path', 'admin'), '/').'/r/'.$resource::slug())
                ->icon('table')
                ->primary()
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

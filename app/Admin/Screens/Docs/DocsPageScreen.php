<?php

namespace App\Admin\Screens\Docs;

use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
use Composer\InstalledVersions;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Screen\Screen;

/**
 * One page of the laravel-admin documentation, rendered inside the panel.
 *
 * A subclass names the page (`concepts/menu`); the text comes from the
 * installed package in the panel's current language, links between pages
 * stay in the panel (linkBase), and the command bar links to the source on
 * GitHub.
 */
abstract class DocsPageScreen extends Screen
{
    /** The page: its path under docs/{locale}/, without `.md`. */
    abstract public static function page(): string;

    public static function slug(): string
    {
        return DocsCatalog::slugFor(static::page());
    }

    public function name(): string
    {
        return $this->library()->page(static::page())['title'];
    }

    public function description(): ?string
    {
        $version = InstalledVersions::getPrettyVersion('dskripchenko/laravel-admin');

        return 'laravel-admin '.$version.' · docs/'.$this->library()->page(static::page())['path'];
    }

    public function query(mixed ...$params): array
    {
        return [];
    }

    public function layout(): array
    {
        return [
            Layout::markdown(fn () => $this->library()->page(static::page())['markdown'])
                ->toc()
                ->linkBase('/'.trim((string) config('admin.path', 'admin'), '/').'/screens/')
                ->imageBase($this->imageBase()),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('Edit on GitHub')
                ->href($this->library()->editUrl(static::page()))
                ->target('_blank')
                ->icon('link'),
        ];
    }

    /** Relative images load from the repository, next to the page's file. */
    private function imageBase(): string
    {
        $path = $this->library()->page(static::page())['path'];

        return str_replace('/blob/', '/raw/', $this->library()->github('blob', 'docs/'.dirname($path).'/'));
    }

    private function library(): DocsLibrary
    {
        return app(DocsLibrary::class);
    }
}

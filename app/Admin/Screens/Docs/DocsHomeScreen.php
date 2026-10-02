<?php

namespace App\Admin\Screens\Docs;

use App\Docs\DocsCatalog;
use Composer\InstalledVersions;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\I18n\Localize;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Screen\Screen;

/**
 * The documentation's table of contents, built from DocsCatalog.
 */
final class DocsHomeScreen extends Screen
{
    public static function slug(): string
    {
        return 'docs';
    }

    public function name(): string
    {
        return __('Documentation');
    }

    public function description(): ?string
    {
        return __('The laravel-admin manual, rendered by the panel it describes.')
            .' v'.InstalledVersions::getPrettyVersion('dskripchenko/laravel-admin');
    }

    public function query(mixed ...$params): array
    {
        return [];
    }

    public function layout(): array
    {
        $sections = [];
        foreach (DocsCatalog::PAGES as $entry) {
            $sections[$entry['group'] ?? ''][] = '- ['.Localize::string($entry['title']).']('.DocsCatalog::slugFor($entry['page']).')';
        }

        $markdown = __('Every page below is a screen of this panel: `Layout::markdown()` renders the Markdown files the package ships in `docs/`, in the language you picked in the top bar.')."\n\n";
        foreach ($sections as $group => $lines) {
            $markdown .= '## '.($group === '' ? __('Start here') : Localize::string($group))."\n\n".implode("\n", $lines)."\n\n";
        }

        return [
            Layout::markdown($markdown)->linkBase('/'.trim((string) config('admin.path', 'admin'), '/').'/screens/')->card(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Link::make('GitHub')->href((string) config('demo.docs_repository'))->target('_blank')->icon('link'),
        ];
    }
}

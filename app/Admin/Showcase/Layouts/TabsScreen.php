<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\ColorPicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Markdown;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Tabs: one form split across tabs. Switching tabs keeps what was
 * typed — the state belongs to the screen, not to the tab.
 */
final class TabsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-tabs';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public function name(): string
    {
        return 'Tabs';
    }

    public function description(): ?string
    {
        return 'Layout::tabs() — a long form in sections, with shared state.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'title' => 'Spring collection',
            'status' => 'draft',
            'body' => "## Fresh arrivals\n\nLight jackets, bright sneakers and **20% off** the first week.",
            'meta_title' => 'Spring collection — Demo Shop',
            'meta_description' => 'Light jackets, bright sneakers and more.',
            'accent' => '#6366f1',
            'show_banner' => true,
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::tabs([
                'Content' => [
                    Input::make('title')->required(),
                    Select::make('status')->options(['draft' => 'Draft', 'published' => 'Published']),
                    Markdown::make('body')->height('220px'),
                ],
                'SEO' => [
                    Input::make('meta_title')->help('Shown in search results'),
                    Textarea::make('meta_description')->rows(3),
                ],
                'Appearance' => [
                    ColorPicker::make('accent')->title('Accent colour'),
                    Switcher::make('show_banner')->title('Show the banner'),
                ],
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

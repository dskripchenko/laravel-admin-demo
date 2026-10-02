<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Action\DropDown;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Actions › Buttons and links: the command bar of a screen. A Button calls a
 * public method of the screen, a Link navigates, a DropDown groups both.
 */
final class ButtonsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-actions-buttons';
    }

    public static function group(): string
    {
        return 'actions';
    }

    public static function icon(): string
    {
        return 'command';
    }

    public function name(): string
    {
        return 'Buttons and links';
    }

    public function description(): ?string
    {
        return 'Button, Link and DropDown in the command bar — variants, icons, targets.';
    }

    protected function demo(): array
    {
        return [
            Layout::markdown(implode("\n", [
                '| '.__('Action').' | '.__('What it does').' |',
                '|---|---|',
                '| **'.__('Save').'** | '.__('A primary Button: calls the `save()` method of this screen.').' |',
                '| **'.__('Discard').'** | '.__('A destructive Button: the red variant, for operations that lose something.').' |',
                '| **'.__('Docs').'** | '.__('A Link to a page of the panel: the router opens it without a reload.').' |',
                '| **GitHub** | '.__('A Link with `target(\'_blank\')`: an external page in a new tab.').' |',
                '| **'.__('Sample CSV').'** | '.__('A Link with the `download` attribute: the browser saves the file.').' |',
                '| **'.__('More').'** | '.__('A DropDown: a menu of nested buttons and links.').' |',
            ]))->card(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Save')->method('save')->primary()->icon('check'),
            Button::make('Discard')->method('discard')->destructive()->icon('trash-2'),
            Link::make('Docs')->href('/admin/screens/docs-concepts-actions')->icon('book-open'),
            Link::make('GitHub')->href('https://github.com/dskripchenko/laravel-admin')->target('_blank')->icon('link'),
            Link::make('Sample CSV')->href(SampleCsv::url())->download('showcase-sample.csv')->icon('download'),
            DropDown::make('More')->icon('settings')->items([
                Button::make('Duplicate')->method('duplicate')->icon('copy'),
                Button::make('Archive')->method('archive')->icon('archive'),
                Link::make('Open the showcase')->href('/admin/screens/showcase')->icon('sparkles'),
            ]),
        ];
    }

    public function save(): array
    {
        return ['message' => __('Saved — well, pretend so: the showcase writes nothing.')];
    }

    public function discard(): array
    {
        return ['message' => __('Changes discarded.')];
    }

    public function duplicate(): array
    {
        return ['message' => __('A copy would be made here.')];
    }

    public function archive(): array
    {
        return ['message' => __('The record would go to the archive.')];
    }
}

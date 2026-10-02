<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Permission\PermissionCheck;

/**
 * Layouts › Rows and the grid: a Rows layout is a 12-column grid, and span()
 * says how many columns a field takes. Layouts nest to any depth, a wrapper
 * adds an element without a frame, and canSee() drops a part for some users.
 */
final class RowsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-rows';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'layout-grid';
    }

    public function name(): string
    {
        return 'Rows and the grid';
    }

    public function description(): ?string
    {
        return 'span() on the 12-column grid, nesting, wrappers and canSee().';
    }

    public function query(mixed ...$params): array
    {
        return [
            'title' => 'Linen shirt',
            'sku' => 'LS-1042',
            'price' => 49.9,
            'stock' => 120,
            'weight' => 0.3,
            'city' => 'London',
            'zip' => 'NW1 6XE',
            'country' => 'GB',
            'released_on' => '2026-04-01',
            'notes' => '',
            'is_featured' => true,
            'cost' => 18.5,
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('The 12-column grid', [
                Layout::rows([
                    Input::make('title')->span(8)->help('span(8)'),
                    Input::make('sku')->title('SKU')->span(4)->help('span(4)'),
                    Number::make('price')->step(0.01)->span(4)->help('span(4)'),
                    Number::make('stock')->integer()->span(4)->help('span(4)'),
                    Number::make('weight')->step(0.1)->span(4)->help('span(4)'),
                    Input::make('city')->span(3)->help('span(3)'),
                    Input::make('zip')->title('ZIP')->span(3)->help('span(3)'),
                    Select::make('country')->options(['GB' => 'United Kingdom', 'DE' => 'Germany', 'FR' => 'France'])->span(6)->help('span(6)'),
                    Textarea::make('notes')->rows(2)->help(__('No span: the full width')),
                ]),
            ])->description(__('Fields fill a row left to right and wrap when the twelve columns are used up; on a phone every field takes the full width.')),

            Layout::columns([
                Layout::block('Nested layouts', [
                    Layout::tabs([
                        'Dates' => [
                            Layout::columns([
                                DatePicker::make('released_on')->title('Released'),
                                Switcher::make('is_featured')->title('Featured'),
                            ]),
                        ],
                        'About' => [
                            Layout::markdown(__('A block holds tabs, a tab holds columns, a column holds fields — layouts compose to any depth.')),
                        ],
                    ]),
                ])->description(__('Block › Tabs › Columns › fields')),

                Layout::wrapper([
                    Layout::markdown(__('This text sits in `Layout::wrapper()` rendered as a `<section>`: an element with no frame or padding of its own, for grouping or a host CSS class.'))->card(),
                ])->tag('section')->className('showcase-wrapper'),
            ]),

            Layout::block('Visible to order managers only', [
                Number::make('cost')->title('Purchase cost')->step(0.01)
                    ->help(__('canSee() dropped this block for users without admin.orders.view — they never receive it.')),
            ])->canSee(fn () => PermissionCheck::allows('admin.orders.view')),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

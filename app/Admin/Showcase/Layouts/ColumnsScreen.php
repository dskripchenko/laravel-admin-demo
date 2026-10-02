<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Label;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Columns and blocks: titled sections side by side, with uneven
 * widths. On a narrow screen the columns stack.
 */
final class ColumnsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-columns';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public function name(): string
    {
        return 'Columns and blocks';
    }

    public function description(): ?string
    {
        return 'Layout::columns() with ratios, and titled Layout::block() sections.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'company' => 'Acme Ltd',
            'vat' => 'GB123456789',
            'address' => "221B Baker Street\nLondon NW1 6XE",
            'contact' => 'Ada Lovelace',
            'role' => 'cto',
            'phone' => '+44 20 7946 0000',
            'customer_since' => '2021-04-12',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Company', [
                    Input::make('company')->required(),
                    Input::make('vat')->title('VAT number'),
                    Textarea::make('address')->rows(3),
                ])->icon('building')->description('Two thirds of the width'),
                Layout::block('Contact', [
                    Input::make('contact')->title('Name'),
                    Select::make('role')->options(['ceo' => 'CEO', 'cto' => 'CTO', 'cfo' => 'CFO']),
                    Input::make('phone')->type('tel'),
                    Label::make('customer_since')->title('Customer since'),
                ])->icon('user')->description('One third'),
            ])->ratios([2, 1]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\ColorPicker;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Rating;
use Dskripchenko\LaravelAdmin\Field\Slider;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Illuminate\Support\Number as NumberFormat;

/**
 * Forms › Numbers and colours: numbers typed, slid and starred, and colours
 * in three formats.
 */
final class NumbersScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-numbers';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'sliders';
    }

    public function name(): string
    {
        return 'Numbers and colours';
    }

    public function description(): ?string
    {
        return 'Number, Slider, Rating and ColorPicker.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'price' => 49.99,
            'quantity' => 3,
            'discount' => 15,
            'volume' => 60,
            'quality' => 4.5,
            'brand_color' => '#6366f1',
            'overlay' => 'rgba(15, 23, 42, 0.6)',
            'accent' => 'hsl(160, 84%, 39%)',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Numbers', [
                    Layout::rows([
                        Number::make('price')->min(0)->max(99999)->step(0.01)->span(6),
                        Number::make('quantity')->integer()->min(1)->max(99)->span(6),
                        Slider::make('discount')->title('Discount, %')->min(0)->max(50)->step(5)
                            ->marks([0 => '0', 25 => '25', 50 => '50']),
                        Slider::make('volume')->min(0)->max(100)->step(10)->marks([0 => 'Off', 100 => 'Max']),
                        Rating::make('quality')->count(5)->half(),
                    ]),
                ])->icon('hash'),
                Layout::block('Colours', [
                    Layout::rows([
                        ColorPicker::make('brand_color')->title('Brand colour (hex)')->format('hex')
                            ->palette(['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9']),
                        ColorPicker::make('overlay')->title('Overlay (rgb with alpha)')->format('rgb')->withAlpha()
                            ->help('The picker shows hex; the value is stored as rgba()'),
                        ColorPicker::make('accent')->title('Accent (hsl)')->format('hsl')->help('Stored as hsl()'),
                    ]),
                ])->icon('palette'),
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make('Submit')->method('submit')->primary()->icon('send')];
    }

    /** @param array<string, mixed> $state */
    public function submit(array $state): array
    {
        validator($state, [
            'price' => 'required|numeric|min:0|max:99999',
            'quantity' => 'required|integer|min:1|max:99',
            'discount' => 'numeric|between:0,50',
            'quality' => 'numeric|between:0,5',
            'brand_color' => ['required', 'regex:/^#[0-9a-f]{6}$/i'],
        ])->validate();

        $total = round((float) $state['price'] * (int) $state['quantity'] * (1 - (float) ($state['discount'] ?? 0) / 100), 2);

        // Money in the panel's language: its separators and the currency's sign.
        $money = NumberFormat::currency($total, 'USD', app()->getLocale());

        return ['message' => __('Valid! The total would be :total.', ['total' => $money])];
    }
}

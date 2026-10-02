<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Accordion: collapsible sections. One at a time by default, several
 * with multi(); a section may start open.
 */
final class AccordionScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-accordion';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'list';
    }

    public function name(): string
    {
        return 'Accordion';
    }

    public function description(): ?string
    {
        return 'Layout::accordion() — collapsible sections, one or several open.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'store_name' => 'Demo Shop',
            'currency' => 'USD',
            'free_shipping_from' => 50,
            'express' => true,
            'tax_rate' => 20,
            'prices_include_tax' => true,
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Frequently asked', [
                    Layout::accordion([
                        'How long does delivery take?' => [
                            Layout::markdown(__('Two to four working days within the country, a week abroad.')),
                        ],
                        'Can I return an item?' => [
                            Layout::markdown(__('Yes — within **30 days**, unused and in its box.')),
                        ],
                        'Which payment methods do you take?' => [
                            Layout::markdown(__('Cards, PayPal and invoices for companies.')),
                        ],
                    ]),
                ])->description(__('One section at a time: opening one closes the other.')),

                Layout::block('Store settings', [
                    Layout::accordion()
                        ->section('General', [
                            Input::make('store_name')->required(),
                            Select::make('currency')->options(['USD' => 'US dollar', 'EUR' => 'Euro', 'GBP' => 'Pound sterling']),
                        ], defaultOpen: true)
                        ->section('Shipping', [
                            Number::make('free_shipping_from')->title('Free shipping from')->min(0),
                            Switcher::make('express')->title('Offer express delivery'),
                        ])
                        ->section('Taxes', [
                            Number::make('tax_rate')->title('Tax rate, %')->min(0)->max(100),
                            Switcher::make('prices_include_tax')->title('Prices include tax'),
                        ], defaultOpen: true)
                        ->multi(),
                ])->description(__('multi(): several sections open at once; two of them start open.')),
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

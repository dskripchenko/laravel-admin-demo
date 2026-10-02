<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use App\Enums\ProductStatus;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Label;
use Dskripchenko\LaravelAdmin\Field\Markdown;
use Dskripchenko\LaravelAdmin\Field\Rating;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Infolist\BadgeEntry;
use Dskripchenko\LaravelAdmin\Infolist\ColorEntry;
use Dskripchenko\LaravelAdmin\Infolist\FieldEntry;
use Dskripchenko\LaravelAdmin\Infolist\IconEntry;
use Dskripchenko\LaravelAdmin\Infolist\ImageEntry;
use Dskripchenko\LaravelAdmin\Infolist\KeyValueEntry;
use Dskripchenko\LaravelAdmin\Infolist\MapEntry;
use Dskripchenko\LaravelAdmin\Infolist\RelationEntry;
use Dskripchenko\LaravelAdmin\Infolist\RepeatableEntry;
use Dskripchenko\LaravelAdmin\Infolist\TextEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Read-only views: a record shown, not edited. An infolist draws the
 * screen's state with an entry per value — text, badges, icons, images,
 * relations, nested lists — and form fields can be locked with readonly()
 * and disabled().
 */
final class ReadOnlyScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-read-only';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'eye';
    }

    public function name(): string
    {
        return 'Read-only views';
    }

    public function description(): ?string
    {
        return 'Layout::infolist() with every entry type, and readonly()/disabled() fields.';
    }

    public function query(mixed ...$params): array
    {
        $product = Product::query()->with('category')->where('status', ProductStatus::Active->value)->whereNotNull('published_at')->orderBy('id')->first()
            ?? Product::query()->with('category')->orderBy('id')->firstOrFail();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'price' => (float) $product->price,
            'status' => $product->status->value,
            'is_featured' => (bool) $product->is_featured,
            'published_at' => $product->published_at?->toDateTimeString(),
            'cover_url' => $product->cover_url,
            'category' => ['id' => $product->category?->id, 'name' => $product->category?->name],
            'accent' => '#6366f1',
            'specs' => ['Weight' => '1.2 kg', 'Warranty' => '2 years', 'Origin' => 'Germany'],
            'variants' => [
                ['name' => 'Black', 'stock' => 12],
                ['name' => 'Silver', 'stock' => 3],
            ],
            'warehouse' => ['lat' => 51.5072, 'lng' => -0.1276],
            'description' => (string) $product->description,
            'rating' => (float) ($product->rating ?? 4.5),
            'stock' => $product->stock,
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('An infolist', [
                Layout::infolist([
                    TextEntry::make('name')->label('Product'),
                    TextEntry::make('sku')->label('SKU')->copyable(),
                    TextEntry::make('price')->label('Price')->asMoney('USD'),
                    TextEntry::make('published_at')->label('Published')->asDateTime('d.m.Y H:i'),
                    BadgeEntry::make('status')->label('Status')->colors(ProductStatus::colors())->labels(ProductStatus::options()),
                    IconEntry::make('is_featured')->label('Featured')
                        ->trueIcon('check-circle')->falseIcon('x-circle')->trueLabel('Yes')->falseLabel('No'),
                    RelationEntry::make('category')->label('Category')->relation('category')->display('name')->linkTo('product-categories'),
                    ColorEntry::make('accent')->label('Accent colour')->showValue(),
                    ImageEntry::make('cover_url')->label('Cover')->size(160, 160)->rounded()->clickToZoom(),
                    KeyValueEntry::make('specs')->label('Specifications')->keyLabel('Property')->valueLabel('Value'),
                    RepeatableEntry::make('variants')->label('Variants')->entries([
                        TextEntry::make('name')->label('Colour'),
                        TextEntry::make('stock')->label('In stock'),
                    ])->layout('columns'),
                    MapEntry::make('warehouse')->label('Warehouse'),
                    FieldEntry::fromField(Rating::make('rating')->count(5)->half()),
                    FieldEntry::fromField(Markdown::make('description')),
                ])->layout('grid'),
            ])->icon('eye')->description('The same values, drawn for reading.'),
            Layout::block('Locked form fields', [
                Layout::rows([
                    Label::make('id')->title('Record ID')->span(4),
                    Input::make('sku')->title('SKU')->readonly()->help('readonly(): focusable and submitted')->span(4),
                    Input::make('stock')->disabled()->help('disabled(): greyed out')->span(4),
                    Select::make('status')->options(ProductStatus::options())->readonly()->span(6)
                        ->help('A read-only select is shown disabled'),
                    Switcher::make('is_featured')->title('Featured')->disabled()->span(6),
                ]),
            ])->icon('lock'),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

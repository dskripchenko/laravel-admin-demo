<?php

namespace App\Admin\Showcase\Grids;

use App\Enums\ProductStatus;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Grids › Columns and formatting: the presets of TableColumn, in the live
 * table and in a small preview on this page.
 */
final class GridColumnsScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-columns';
    }

    public static function icon(): string
    {
        return 'columns';
    }

    protected static function resource(): string
    {
        return GridColumnsResource::class;
    }

    public function name(): string
    {
        return 'Columns';
    }

    public function description(): ?string
    {
        return 'Images, money, badges, booleans, dates, links, sizes and custom formatters.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'preview' => Product::query()->orderBy('id')->limit(5)->get()
                ->map(fn (Product $product) => $product->only(['cover_url', 'name', 'sku', 'status', 'price', 'is_featured', 'published_at']))
                ->all(),
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('What to try', [
                Layout::markdown(__('Open the table and look at the cells: every column is one preset of `TableColumn`. Copy a SKU with its button, hide and show columns from the toolbar (the name cannot be hidden: `cantHide()`; "Updated" starts hidden: `defaultHidden()`), sort by price or stock and see the summary row under the table — average and range of the price, total stock — follow the filters. The description column shows the size of the text: `format()` turns the value into a number on the server, `asBytes()` prints it. Dates take PHP `date()` formats, with month names in the panel language.')),
            ]),
            Layout::block('Preview', [
                RelationTable::make('preview')->title('The first five products')->columns([
                    TableColumn::make('cover_url')->label('Image')->asImage(32, 32)->width('56px'),
                    TableColumn::make('name'),
                    TableColumn::make('sku')->label('SKU'),
                    TableColumn::make('status')->asBadge(ProductStatus::colors(), ProductStatus::options()),
                    TableColumn::make('price')->asMoney('USD')->align('right'),
                    TableColumn::make('is_featured')->label('Featured')->asBoolean(),
                    TableColumn::make('published_at')->label('Published')->asDate('j M Y'),
                ]),
            ])->description('A RelationTable field on a screen draws its rows with the same presets.'),
        ];
    }
}

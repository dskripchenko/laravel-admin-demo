<?php

namespace App\Admin\Showcase\Grids;

use App\Enums\ProductStatus;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Grids › Columns: one column per preset. With no fields() the resource is a
 * read-only list — no create button, no edit form.
 */
final class GridColumnsResource extends Resource
{
    public static string $model = Product::class;

    public static string $icon = 'table';

    public static function slug(): string
    {
        return 'showcase-grid-columns';
    }

    public static function label(): string
    {
        return 'Columns and formatting';
    }

    /** The same permissions as the products section. */
    public static function permission(): string
    {
        return 'admin.products';
    }

    public function fields(): array
    {
        return [];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('cover_url')->label('Image')->asImage(40, 40)->width('64px'),
            TableColumn::make('name')->sort()->search()->cantHide()->width('260px'),
            TableColumn::make('sku')->label('SKU')->copyable(),
            TableColumn::make('status')->asBadge(ProductStatus::colors(), ProductStatus::options()),
            TableColumn::make('price')->asMoney('USD')->align('right')->sort()->summary(['avg', 'range']),
            TableColumn::make('stock')->align('right')->sort()->summary(['sum']),
            TableColumn::make('is_featured')->label('Featured')->asBoolean('Featured', 'Regular')->align('center'),
            TableColumn::make('rating')->align('right')
                ->format(fn (mixed $value) => $value === null ? '—' : number_format((float) $value, 1).' ★'),
            TableColumn::make('description_size')->label('Description')->asBytes()->align('right'),
            TableColumn::make('edit_url')->label('Link')->asLink('{edit_url}'),
            TableColumn::make('published_at')->label('Published')->asDate('d.m.Y')->sort(),
            TableColumn::make('updated_at')->label('Updated')->asDateTime('d.m.Y H:i')->sort()->defaultHidden(),
        ];
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with(['category', 'cover.variants']);
    }

    /** Cells read flat keys of the row: computed values are added here. */
    public function transformRecord(Model $record): array
    {
        return parent::transformRecord($record) + [
            'description_size' => strlen((string) $record->getAttribute('description')),
            'edit_url' => '/'.trim((string) config('admin.path', 'admin'), '/').'/r/products/'.$record->getKey().'/edit',
        ];
    }
}

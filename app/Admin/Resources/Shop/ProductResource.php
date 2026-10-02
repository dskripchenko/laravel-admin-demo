<?php

namespace App\Admin\Resources\Shop;

use App\Enums\ProductStatus;
use App\Models\Shop\Category;
use App\Models\Shop\Product;
use Dskripchenko\LaravelAdmin\Action\BulkAction;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Markdown;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\TreeSelect;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Filter\QueryFilter;
use Dskripchenko\LaravelAdmin\Filter\SelectFromModelFilter;
use Dskripchenko\LaravelAdmin\Filter\SwitcherFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Dskripchenko\LaravelAdminMedia\Fields\MediaPicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class ProductResource extends Resource
{
    public static string $model = Product::class;

    public static string $icon = 'package';

    public static function slug(): string
    {
        return 'products';
    }

    public static function label(): string
    {
        return 'Products';
    }

    /** One record's name, for titles, confirmations and toasts: "Create product". */
    public static function singularLabel(): ?string
    {
        return 'product';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required()->span(8),
            Input::make('sku')->title('SKU')->required()->span(4),
            Slug::make('slug')->from('name')->required(),
            TreeSelect::make('category_id')->title('Category')->fromModel(Category::class)->required(),
            Number::make('price')->min(0)->step(0.01)->required()->span(4),
            Number::make('compare_at_price')->title('Compare-at price')->min(0)->step(0.01)->span(4),
            Number::make('stock')->integer()->min(0)->required()->span(4),
            Select::make('status')->options(ProductStatus::options())->required()->span(6),
            DatePicker::make('published_at')->span(6),
            Switcher::make('is_featured')->title('Featured'),
            MediaPicker::make('cover_id')->title('Cover')->images()->collection('products')->responsiveSet('product'),
            MediaPicker::make('gallery')->images()->collection('products')->responsiveSet('product')->multiple()->maxItems(8),
            Markdown::make('description')->height('240px'),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('cover_url')->label('Cover')->asImage(40, 40),
            TableColumn::make('name')->sort()->search(),
            TableColumn::make('sku')->label('SKU')->search()->copyable(),
            TableColumn::make('category_name')->label('Category'),
            TableColumn::make('price')->asMoney('USD')->align('right')->sort(),
            TableColumn::make('stock')->align('right')->sort()->editable(['integer', 'min:0'], 'number'),
            TableColumn::make('status')->asBadge(ProductStatus::colors()),
            TableColumn::make('is_featured')->label('Featured')->asBoolean()->defaultHidden(),
            TableColumn::make('rating')->align('right')->sort()->defaultHidden(),
            TableColumn::make('updated_at')->label('Updated')->asDateTime()->sort()->defaultHidden(),
        ];
    }

    public function filters(): array
    {
        return [
            OptionsFilter::for('status')->options(ProductStatus::options())->multiple(),
            SelectFromModelFilter::for('category_id')->label('Category')->fromModel(Category::class, 'name'),
            SwitcherFilter::for('is_featured')->label('Featured'),
            QueryFilter::for('stock_level')->label('Low stock (< 10)')->as('switcher')
                ->using(fn (Builder $query, mixed $value) => filter_var($value, FILTER_VALIDATE_BOOL) ? $query->where('stock', '<', 10) : $query),
        ];
    }

    public function actions(): array
    {
        return [
            BulkAction::make('Publish')->method('publish')->icon('check'),
            BulkAction::make('Archive')->method('archive')->icon('archive')->confirm('Archive the selected products?'),
        ];
    }

    /** @param list<int> $ids */
    public function publish(array $ids): int
    {
        return Product::query()->whereKey($ids)->update([
            'status' => ProductStatus::Active->value,
            'published_at' => now(),
        ]);
    }

    /** @param list<int> $ids */
    public function archive(array $ids): int
    {
        return Product::query()->whereKey($ids)->update(['status' => ProductStatus::Archived->value]);
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with(['category', 'cover.variants']);
    }

    public function searchableFields(): array
    {
        return ['name', 'sku'];
    }

    public function pickerPreview(Model $row): ?string
    {
        return $row->getAttribute('cover_url');
    }
}

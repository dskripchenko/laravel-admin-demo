<?php

namespace App\Admin\Resources\Shop;

use App\Models\Shop\Category;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Field\TreeSelect;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;

/**
 * Product categories. The model's parent()/children() relations turn the
 * index into a tree.
 */
final class ProductCategoryResource extends Resource
{
    public static string $model = Category::class;

    public static string $icon = 'folder-tree';

    public static function slug(): string
    {
        return 'product-categories';
    }

    public static function label(): string
    {
        return 'Categories';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required(),
            Slug::make('slug')->from('name')->required(),
            TreeSelect::make('parent_id')->title('Parent category')->fromModel(Category::class),
            Textarea::make('description')->rows(3),
            Number::make('position')->integer()->min(0),
            Switcher::make('is_visible')->title('Visible in the store')->default(true),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name')->sort()->search(),
            TableColumn::make('slug'),
            TableColumn::make('is_visible')->label('Visible')->asBoolean(),
            TableColumn::make('position')->sort(),
        ];
    }

    public function defaultOrder(): array
    {
        return [['column' => 'position', 'direction' => 'asc']];
    }
}

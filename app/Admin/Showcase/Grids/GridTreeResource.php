<?php

namespace App\Admin\Showcase\Grids;

use App\Models\Shop\Category;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\TreeSelect;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Model;

/**
 * Grids › Tree view: the model's parent()/children() relations make the
 * index a tree — no setting needed. Per-node actions add a shortcut.
 */
final class GridTreeResource extends Resource
{
    public static string $model = Category::class;

    public static string $icon = 'folder-tree';

    public static function slug(): string
    {
        return 'showcase-grid-tree';
    }

    public static function label(): string
    {
        return 'Category tree';
    }

    public static function permission(): string
    {
        return 'admin.product-categories';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required(),
            Slug::make('slug')->from('name')->required(),
            TreeSelect::make('parent_id')->title('Parent category')->fromModel(Category::class),
            Number::make('position')->integer()->min(0),
            Switcher::make('is_visible')->title('Visible in the store')->default(true),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name')->search(),
            TableColumn::make('slug'),
            TableColumn::make('is_visible')->label('Visible')->asBoolean(),
        ];
    }

    /** Forces the tree; null would switch it off although the relations exist. */
    public function hierarchyParentKey(): ?string
    {
        return 'parent_id';
    }

    /**
     * Shown in the toolbar of the selected node: open the create form with
     * the parent filled in (`?parent_id=` pre-fills the form).
     */
    public function treeNodeActions(Model $row): array
    {
        return [[
            'id' => 'add-child',
            'label' => 'Add a subcategory',
            'icon' => 'plus',
            'variant' => 'secondary',
            'kind' => 'navigate',
            'to' => ['slug' => self::slug(), 'screen' => 'create', 'params' => ['parent_id' => '{id}']],
        ]];
    }

    public function defaultOrder(): array
    {
        return [['column' => 'position', 'direction' => 'asc']];
    }
}

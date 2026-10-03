<?php

namespace App\Admin\Showcase\Grids;

use App\Models\Blog\Category;
use Dskripchenko\LaravelAdmin\Field\ColorPicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Grids › Reorder and replicate: rows dragged by a handle into a `position`
 * column, and a copy action that keeps the unique slug unique.
 */
final class GridReorderResource extends Resource
{
    public static string $model = Category::class;

    public static string $icon = 'list';

    public static function slug(): string
    {
        return 'showcase-grid-reorder';
    }

    public static function label(): string
    {
        return 'Reorder and replicate';
    }

    /** One record's name, for titles, confirmations and toasts: "Create category". */
    public static function singularLabel(): ?string
    {
        return 'category';
    }

    public static function permission(): string
    {
        return 'admin.blog-categories';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required(),
            Slug::make('slug')->from('name')->required(),
            ColorPicker::make('color'),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name'),
            TableColumn::make('slug'),
            TableColumn::make('posts_count')->label('Posts')->align('right'),
            TableColumn::make('position')->align('right'),
        ];
    }

    /** A drag handle on every row; the order goes into position(). */
    public function reorderable(): bool
    {
        return true;
    }

    public function reorderColumn(): string
    {
        return 'position';
    }

    /** A "Duplicate" action on every row. */
    public function replicable(): bool
    {
        return true;
    }

    public function replicate(Model $original): Model
    {
        $copy = $original->replicate();
        $copy->setAttribute('name', $original->getAttribute('name').' (copy)');
        $copy->setAttribute('slug', $original->getAttribute('slug').'-'.Str::lower(Str::random(4)));
        $copy->setAttribute('position', (int) Category::query()->max('position') + 1);

        return $copy;
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->withCount('posts');
    }
}

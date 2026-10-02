<?php

namespace App\Admin\Resources\Blog;

use App\Models\Blog\Category;
use Dskripchenko\LaravelAdmin\Field\ColorPicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;

final class BlogCategoryResource extends Resource
{
    public static string $model = Category::class;

    public static string $icon = 'folder';

    public static function slug(): string
    {
        return 'blog-categories';
    }

    public static function label(): string
    {
        return 'Blog categories';
    }

    /** One record's name, for titles, confirmations and toasts: "Create blog category". */
    public static function singularLabel(): ?string
    {
        return 'blog category';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required(),
            Slug::make('slug')->from('name')->required(),
            ColorPicker::make('color')->palette(['#6366f1', '#10b981', '#f59e0b', '#ef4444', '#0ea5e9', '#8b5cf6']),
            Textarea::make('description')->rows(3),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name')->sort()->search(),
            TableColumn::make('slug'),
            TableColumn::make('color'),
            TableColumn::make('posts_count')->label('Posts')->align('right')->sort(),
        ];
    }

    public function reorderable(): bool
    {
        return true;
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->withCount('posts');
    }

    public function defaultOrder(): array
    {
        return [['column' => 'position', 'direction' => 'asc']];
    }
}

<?php

namespace App\Admin\Resources\Blog;

use App\Models\Blog\Tag;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;

final class TagResource extends Resource
{
    public static string $model = Tag::class;

    public static string $icon = 'tag';

    public static function slug(): string
    {
        return 'tags';
    }

    public static function label(): string
    {
        return 'Tags';
    }

    /** One record's name, for titles, confirmations and toasts: "Create tag". */
    public static function singularLabel(): ?string
    {
        return 'tag';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required(),
            Slug::make('slug')->from('name')->required(),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name')->sort()->search()->editable(['required', 'max:60']),
            TableColumn::make('slug')->search(),
            TableColumn::make('posts_count')->label('Posts')->align('right')->sort(),
        ];
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->withCount('posts');
    }
}

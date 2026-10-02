<?php

namespace App\Admin\Showcase\Grids;

use App\Enums\PostStatus;
use App\Models\Blog\Post;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Grids › Inline editing: a cell becomes an input on click and saves itself.
 * Each editable column carries its own validation rules and input type.
 */
final class GridInlineResource extends Resource
{
    public static string $model = Post::class;

    public static string $icon = 'pencil';

    public static function slug(): string
    {
        return 'showcase-grid-inline';
    }

    public static function label(): string
    {
        return 'Inline editing';
    }

    public static function permission(): string
    {
        return 'admin.posts';
    }

    public function fields(): array
    {
        return [];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('title')->search()
                ->editable(['required', 'string', 'min:3', 'max:160']),
            TableColumn::make('status')->asBadge(PostStatus::colors(), PostStatus::options())
                ->editable(['required', 'in:draft,review,published'], 'select', PostStatus::options()),
            TableColumn::make('views')->align('right')->sort()
                ->editable(['required', 'integer', 'min:0'], 'number'),
            TableColumn::make('published_at')->label('Published')->asDate('d.m.Y')->sort()
                ->editable(['nullable', 'date'], 'date'),
            TableColumn::make('is_featured')->label('Featured')->asBoolean()
                ->editable(['boolean'], 'switcher'),
            TableColumn::make('excerpt')
                ->editable(['nullable', 'string', 'max:500'], 'textarea'),
        ];
    }

    /**
     * A per-row lock on top of the column's editable(): the title of a
     * published post stays as it is.
     */
    public function editableForRow(Model $row, string $column): bool
    {
        return ! ($column === 'title' && $row->getAttribute('status') === PostStatus::Published);
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with(['author', 'category']);
    }

    public function defaultOrder(): array
    {
        return [['column' => 'created_at', 'direction' => 'desc']];
    }
}

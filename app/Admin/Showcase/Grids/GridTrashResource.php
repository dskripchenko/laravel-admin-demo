<?php

namespace App\Admin\Showcase\Grids;

use App\Enums\PostStatus;
use App\Models\Blog\Post;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Grids › Soft deletes and polling: the Post model uses SoftDeletes, so the
 * table gets a trashed filter and restore / delete-forever actions by itself.
 * polling() refreshes the rows every 30 seconds.
 */
final class GridTrashResource extends Resource
{
    public static string $model = Post::class;

    public static string $icon = 'trash-2';

    public static function slug(): string
    {
        return 'showcase-grid-trash';
    }

    public static function label(): string
    {
        return 'Soft deletes and polling';
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
            TableColumn::make('title')->search(),
            TableColumn::make('author_name')->label('Author'),
            TableColumn::make('status')->asBadge(PostStatus::colors(), PostStatus::options()),
            TableColumn::make('deleted_at')->label('Deleted')->asDateTime('d.m.Y H:i'),
        ];
    }

    public function polling(): ?int
    {
        return 30;
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with('author');
    }

    public function defaultOrder(): array
    {
        return [['column' => 'created_at', 'direction' => 'desc']];
    }
}

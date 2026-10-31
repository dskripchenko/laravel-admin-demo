<?php

namespace App\Admin\Showcase\Grids;

use App\Enums\PostStatus;
use App\Models\Blog\Post;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Resource\Views\CalendarView;
use Dskripchenko\LaravelAdmin\Resource\Views\KanbanView;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Grids › Kanban and calendar: the blog's editorial board. The same posts
 * as a list, as cards in a column per status, and on a calendar by the day
 * they are published; the switcher above the table picks the view, and the
 * search and filters apply to all three.
 */
final class GridViewsResource extends Resource
{
    public static string $model = Post::class;

    public static string $icon = 'layout-grid';

    public static function slug(): string
    {
        return 'showcase-grid-views';
    }

    public static function label(): string
    {
        return 'Editorial board';
    }

    /** One record's name, for titles, confirmations and toasts: "Create post". */
    public static function singularLabel(): ?string
    {
        return 'post';
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
            TableColumn::make('status')->asBadge(PostStatus::colors(), PostStatus::options()),
            TableColumn::make('author_name')->label('Author'),
            TableColumn::make('published_at')->label('Published')->asDate('d.m.Y')->sort(),
        ];
    }

    /** A column per status; dragging a card changes the post's status. */
    public function kanban(): ?KanbanView
    {
        return KanbanView::make('status')
            ->columns(PostStatus::options())
            ->title('title')
            ->tags('category.name')
            ->assignee('author.name')
            ->wip([PostStatus::Review->value => 5])
            ->limit(12);
    }

    /** The posts on the day they are published; dragging one reschedules it. */
    public function calendar(): ?CalendarView
    {
        return CalendarView::make('published_at')
            ->title('title')
            ->color('status', PostStatus::colors())
            ->views(['month', 'week', 'agenda'])
            ->initialView('month');
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with(['author', 'category']);
    }

    public function defaultOrder(): array
    {
        return [['column' => 'published_at', 'direction' => 'desc']];
    }
}

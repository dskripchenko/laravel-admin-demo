<?php

namespace App\Admin\Showcase\Grids;

use App\Models\Blog\Author;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Layout\ResourceTable;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Grids › Embedded tables: the edit page of an author has a tab with the
 * author's posts — another resource's table, filtered by the foreign key.
 */
final class GridEmbedAuthorsResource extends Resource
{
    public static string $model = Author::class;

    public static string $icon = 'users';

    public static function slug(): string
    {
        return 'showcase-grid-authors';
    }

    public static function label(): string
    {
        return 'Authors and their posts';
    }

    /** One record's name, for titles, confirmations and toasts: "Create author". */
    public static function singularLabel(): ?string
    {
        return 'author';
    }

    public static function permission(): string
    {
        return 'admin.authors';
    }

    public function fields(): array
    {
        return [
            Input::make('name')->required()->span(6),
            Input::make('email')->type('email')->required()->span(6),
            Input::make('title')->title('Job title'),
            Textarea::make('bio')->rows(3),
            Switcher::make('is_active')->title('Active'),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('name')->sort()->search(),
            TableColumn::make('title')->label('Job title'),
            TableColumn::make('posts_count')->label('Posts')->align('right')->sort(),
        ];
    }

    /**
     * The create form keeps the plain list of fields: a new author has no
     * id yet, so there would be no posts to show.
     */
    public function formLayout(string $context): array
    {
        if ($context !== 'update') {
            return [];
        }

        return [
            Layout::tabs([
                'Profile' => [Layout::rows($this->fields())],
                'Posts' => [
                    ResourceTable::for(GridEmbedPostsResource::class)
                        ->foreignKey('author_id')
                        ->hideColumns(['author_id'])
                        ->features(['create' => true, 'delete' => true, 'bulkDelete' => true]),
                ],
            ]),
        ];
    }

    public function recordTitle(Model $row): string
    {
        return (string) $row->getAttribute('name');
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->withCount('posts');
    }
}

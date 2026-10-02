<?php

namespace App\Admin\Showcase\Grids;

use App\Enums\PostStatus;
use App\Models\Blog\Post;
use Dskripchenko\LaravelAdmin\Field\Hidden;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Filter\QueryFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * The child of the embedded table: posts, narrowed to one author by the
 * filter the parent's table sends.
 */
final class GridEmbedPostsResource extends Resource
{
    public static string $model = Post::class;

    public static string $icon = 'file-text';

    public static function slug(): string
    {
        return 'showcase-grid-author-posts';
    }

    public static function label(): string
    {
        return 'Posts of an author';
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
        return [
            // Filled in by the embedded table from the parent record.
            Hidden::make('author_id')->rules(['required', 'integer', 'exists:authors,id']),
            Input::make('title')->required(),
            Slug::make('slug')->from('title'),
            Select::make('status')->options(PostStatus::options())->default(PostStatus::Draft->value),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('title')->editable(['required', 'string', 'max:160']),
            TableColumn::make('status')->asBadge(PostStatus::colors(), PostStatus::options())
                ->editable(['required', 'in:draft,review,published'], 'select', PostStatus::options()),
            TableColumn::make('views')->align('right'),
        ];
    }

    /**
     * Without an exact-match filter on the foreign key the embedded table
     * would list every post.
     */
    public function filters(): array
    {
        return [
            QueryFilter::for('author_id')->using(fn (Builder $query, mixed $value) => $query->where('author_id', $value)),
        ];
    }

    /** A post added from the table gets a unique slug from its title. */
    public function fillModel(Model $model, array $data): void
    {
        parent::fillModel($model, $data);
        if (blank($model->getAttribute('slug'))) {
            $model->setAttribute('slug', Str::slug((string) $model->getAttribute('title')).'-'.Str::lower(Str::random(5)));
        }
        if (blank($model->getAttribute('status'))) {
            $model->setAttribute('status', PostStatus::Draft);
        }
    }
}

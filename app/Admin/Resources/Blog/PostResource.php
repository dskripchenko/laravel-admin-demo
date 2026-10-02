<?php

namespace App\Admin\Resources\Blog;

use App\Enums\PostStatus;
use App\Models\Blog\Author;
use App\Models\Blog\Category;
use App\Models\Blog\Post;
use App\Models\Blog\Tag;
use Dskripchenko\LaravelAdmin\Action\BulkAction;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Markdown;
use Dskripchenko\LaravelAdmin\Field\RelationSelect;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Filter\DateRangeFilter;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Filter\SelectFromModelFilter;
use Dskripchenko\LaravelAdmin\Filter\SwitcherFilter;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

final class PostResource extends Resource
{
    public static string $model = Post::class;

    public static string $icon = 'file-text';

    public static function slug(): string
    {
        return 'posts';
    }

    public static function label(): string
    {
        return 'Posts';
    }

    public function fields(): array
    {
        return [
            Input::make('title')->required(),
            Slug::make('slug')->from('title')->required(),
            RelationSelect::make('author_id')->title('Author')->relation(Author::class, 'name')->required()->span(4),
            RelationSelect::make('category_id')->title('Category')->relation(Category::class, 'name')->span(4),
            Select::make('status')->options(PostStatus::options())->required()->span(4),
            Select::make('tag_ids')->title('Tags')->fromModel(Tag::class, 'id', 'name')->multiple()->searchable(),
            Textarea::make('excerpt')->rows(2),
            Markdown::make('body')->height('420px'),
            DatePicker::make('published_at')->withTime()->span(6),
            Switcher::make('is_featured')->title('Featured')->span(6),
        ];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('title')->sort()->search(),
            TableColumn::make('author_name')->label('Author'),
            TableColumn::make('category_name')->label('Category'),
            TableColumn::make('status')->asBadge(PostStatus::colors()),
            TableColumn::make('views')->align('right')->sort(),
            TableColumn::make('reading_minutes')->label('Read, min')->align('right')->defaultHidden(),
            TableColumn::make('is_featured')->label('Featured')->asBoolean()->defaultHidden(),
            TableColumn::make('published_at')->label('Published')->asDate()->sort(),
        ];
    }

    public function filters(): array
    {
        return [
            OptionsFilter::for('status')->options(PostStatus::options()),
            SelectFromModelFilter::for('author_id')->label('Author')->fromModel(Author::class, 'name'),
            SelectFromModelFilter::for('category_id')->label('Category')->fromModel(Category::class, 'name'),
            SwitcherFilter::for('is_featured')->label('Featured'),
            DateRangeFilter::for('published_at')->label('Published'),
        ];
    }

    public function actions(): array
    {
        return [
            BulkAction::make('Publish')->method('publish')->icon('check'),
            BulkAction::make('Back to drafts')->withName('unpublish')->method('unpublish')->icon('rotate-ccw'),
        ];
    }

    /** @param list<int> $ids */
    public function publish(array $ids): int
    {
        return Post::query()->whereKey($ids)->update([
            'status' => PostStatus::Published->value,
            'published_at' => now(),
        ]);
    }

    /** @param list<int> $ids */
    public function unpublish(array $ids): int
    {
        return Post::query()->whereKey($ids)->update(['status' => PostStatus::Draft->value]);
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with(['author', 'category']);
    }

    public function defaultOrder(): array
    {
        return [['column' => 'created_at', 'direction' => 'desc']];
    }

    public function transformRecord(Model $record): array
    {
        return parent::transformRecord($record) + [
            'tag_ids' => $record->tags()->pluck('tags.id')->all(),
        ];
    }

    public function fillModel(Model $model, array $data): void
    {
        if (array_key_exists('tag_ids', $data)) {
            /** @var Post $model */
            $model->pendingTagIds = array_map('intval', (array) $data['tag_ids']);
            unset($data['tag_ids']);
        }
        parent::fillModel($model, $data);
    }
}

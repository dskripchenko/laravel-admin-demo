<?php

namespace App\Models\Blog;

use App\Enums\PostStatus;
use Dskripchenko\LaravelAdmin\Audit\Concerns\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use Loggable, SoftDeletes;

    protected $guarded = ['id'];

    protected $appends = ['author_name', 'category_name'];

    /**
     * Tag ids set by the admin form; synced once the post is saved.
     *
     * @var list<int>|null
     */
    public ?array $pendingTagIds = null;

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'is_featured' => 'boolean',
            'views' => 'integer',
            'reading_minutes' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            $words = str_word_count(strip_tags((string) $post->body));
            $post->reading_minutes = max(1, (int) ceil($words / 200));
        });

        static::saved(function (Post $post): void {
            if ($post->pendingTagIds !== null) {
                $post->tags()->sync($post->pendingTagIds);
                $post->pendingTagIds = null;
            }
        });
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    public function getAuthorNameAttribute(): ?string
    {
        return $this->author?->name;
    }

    public function getCategoryNameAttribute(): ?string
    {
        return $this->category?->name;
    }
}

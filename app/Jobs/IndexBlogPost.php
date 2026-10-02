<?php

namespace App\Jobs;

use App\Models\Blog\Post;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

/**
 * Builds the search document of one blog post. A full reindex is a batch of
 * these, one per post.
 *
 * The demo has no search engine, so the document is built and dropped:
 * running the job again changes nothing.
 */
final class IndexBlogPost implements ShouldQueue
{
    use Batchable, Queueable;

    public function __construct(public int $postId) {}

    /** @return array{id: int, title: string, tags: list<string>, words: int} */
    public function handle(): array
    {
        $post = Post::withTrashed()->with('tags')->findOrFail($this->postId);

        return [
            'id' => $post->id,
            'title' => $post->title,
            'tags' => $post->tags->pluck('name')->values()->all(),
            'words' => Str::wordCount(strip_tags((string) $post->body)),
        ];
    }
}

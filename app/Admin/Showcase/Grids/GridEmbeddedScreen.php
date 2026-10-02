<?php

namespace App\Admin\Showcase\Grids;

use App\Models\Blog\Author;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Grids › Embedded tables.
 */
final class GridEmbeddedScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-embedded';
    }

    public static function icon(): string
    {
        return 'layers';
    }

    protected static function resource(): string
    {
        return GridEmbedAuthorsResource::class;
    }

    public function name(): string
    {
        return 'Embedded tables';
    }

    public function description(): ?string
    {
        return 'A table of child records on the parent’s edit page: inline edits, quick add, delete.';
    }

    public function commandBar(): array
    {
        $author = Author::query()->orderBy('id')->value('id');
        $base = '/'.trim((string) config('admin.path', 'admin'), '/').'/r/'.GridEmbedAuthorsResource::slug();

        return [
            ...parent::commandBar(),
            // The example lives on an author's edit page, so that page is the primary action.
            Link::make('Edit an author')->href("{$base}/{$author}/edit")->icon('pencil')->primary()
                ->permission(GridEmbedAuthorsResource::permission().'.update')
                ->canSee($author !== null),
        ];
    }

    protected function sourceClasses(): array
    {
        return [GridEmbedAuthorsResource::class, GridEmbedPostsResource::class];
    }

    protected function demo(): array
    {
        return [
            Layout::block('What to try', [
                Layout::markdown(__('An embedded table lives on a parent’s edit page, so this example opens as a page of its own: press **Edit an author** at the top and switch to the **Posts** tab: it is the table of another resource, narrowed to this author by the `author_id` filter. Edit a title or a status right in the cell, add a post with the quick-add row (the author is filled in for you), delete one, or select several and delete them together. Every request is checked against the posts resource’s own permissions.')),
            ]),
        ];
    }
}

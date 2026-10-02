<?php

namespace Tests\Feature;

use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;

class DocsTest extends DemoTestCase
{
    public function test_every_catalog_page_renders_as_markdown_with_in_panel_links(): void
    {
        $this->loginAs('viewer');

        foreach (DocsCatalog::PAGES as $entry) {
            $slug = DocsCatalog::slugFor($entry['page']);
            $response = $this->getJson("/api/admin/{$slug}/state")->assertOk();

            $layout = $response->json('payload.layout.0');
            $this->assertSame('markdown', $layout['type'], $slug);
            $this->assertNotEmpty($layout['markdown'], $slug);
            $this->assertSame('/admin/screens/', $layout['props']['linkBase'] ?? null, $slug);
            $this->assertStringNotContainsString('](concepts/', $layout['markdown'], "{$slug}: a link was left unresolved");
            $this->assertNotEmpty($response->json('payload.command_bar'), "{$slug}: no Edit on GitHub link");
        }
    }

    public function test_links_between_pages_point_at_their_screens(): void
    {
        $markdown = app(DocsLibrary::class)->page('concepts/widgets-and-dashboards', 'en')['markdown'];

        $this->assertStringContainsString('](docs-concepts-screens)', $markdown);
        $this->assertStringContainsString('](https://github.com/dskripchenko/laravel-admin/blob/main/docs/ru/api/system.md)', $markdown);
    }

    public function test_the_russian_panel_reads_russian_pages(): void
    {
        $page = app(DocsLibrary::class)->page('getting-started', 'ru');

        $this->assertSame('ru', $page['locale']);
        $this->assertSame('ru/getting-started.md', $page['path']);
    }

    public function test_a_missing_translation_falls_back_to_english(): void
    {
        $library = app(DocsLibrary::class);

        $this->assertSame('en', $library->resolveLocale('getting-started', 'de-missing'));
        $page = $library->page('getting-started', 'xx');
        $this->assertSame('en', $page['locale']);
        $this->assertStringContainsString('has not been translated yet', $page['markdown']);
    }

    public function test_the_docs_home_lists_every_page(): void
    {
        $this->loginAs('editor');

        $markdown = $this->getJson('/api/admin/docs/state')->assertOk()->json('payload.layout.0.markdown');
        foreach (DocsCatalog::PAGES as $entry) {
            $this->assertStringContainsString('('.DocsCatalog::slugFor($entry['page']).')', $markdown);
        }
    }
}

<?php

namespace Tests\Feature;

class EmbedPageTest extends DemoTestCase
{
    public function test_it_serves_the_embed_showcase_page(): void
    {
        $response = $this->get('/embed')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=utf-8');

        $html = file_get_contents($response->baseResponse->getFile()->getPathname());
        $this->assertStringContainsString('cdn.jsdelivr.net/npm/@dskripchenko/embed@1/dist/embed.js', $html);
        $this->assertStringContainsString('<dsk-flow', $html);
    }

    public function test_the_landing_page_links_the_showcase(): void
    {
        $this->get('/?lang=en')->assertOk()->assertSee('href="/embed"', false)->assertSee('Components for any page');
        $this->get('/?lang=ru')->assertOk()->assertSee('Встраивание');
    }

    public function test_the_self_hosted_variant_is_served_only_with_the_mirror(): void
    {
        $mirrored = is_dir(public_path('embed/npm/@dskripchenko'));

        $this->get('/embed/local')->assertStatus($mirrored ? 200 : 404);
    }
}

<?php

namespace Tests\Feature;

use App\Providers\AdminServiceProvider;

class ShowcaseTest extends DemoTestCase
{
    public function test_every_showcase_screen_shows_its_own_source(): void
    {
        $this->loginAs('viewer');

        foreach (AdminServiceProvider::SHOWCASE as $screen) {
            $layout = $this->getJson('/api/admin/'.$screen::slug().'/state')->assertOk()->json('payload.layout');
            $source = collect($layout)->last();

            $this->assertSame('block', $source['type'], $screen);
            $code = json_encode($source);
            $this->assertStringContainsString('"type":"code"', $code, $screen);
            $this->assertStringContainsString(class_basename($screen), $code.file_get_contents((new \ReflectionClass($screen))->getFileName()), $screen);
        }
    }

    public function test_the_form_basics_screen_validates_on_the_server(): void
    {
        $this->loginAs('viewer');

        $this->postJson('/api/admin/showcase-forms-basics/runMethod', [
            'method' => 'submit',
            'payload' => ['name' => '', 'email' => 'nope', 'plan' => 'team'],
        ])->assertStatus(422);

        $this->postJson('/api/admin/showcase-forms-basics/runMethod', [
            'method' => 'submit',
            'payload' => ['name' => 'Ada', 'email' => 'ada@example.com', 'plan' => 'team'],
        ])->assertOk();
    }
}

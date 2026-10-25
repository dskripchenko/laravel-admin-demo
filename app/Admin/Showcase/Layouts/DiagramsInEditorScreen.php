<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\Wysiwyg;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Diagrams in the editor: every diagram type the panel draws, as
 * ```mermaid code blocks inside the WYSIWYG editor. A click on a diagram opens
 * its code with a live preview, `/diagram` inserts one, and the fullscreen
 * button gives the editor the whole window.
 */
final class DiagramsInEditorScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-diagrams-in-editor';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'workflow';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Diagrams in the editor');
    }

    public function description(): ?string
    {
        return __('Every diagram type inside the WYSIWYG editor: click a diagram to edit its code, use the fullscreen button for room.');
    }

    public function query(mixed ...$params): array
    {
        $html = '<h2>'.e(__('Every diagram type the panel draws')).'</h2>'
            .'<p>'.e(__('Each block below is a ```mermaid code block in the stored HTML. Click a diagram to open its code with a live preview; type /diagram on an empty line to add one; the fullscreen button on the toolbar gives the editor the whole window.')).'</p>';

        foreach (DiagramSamples::all() as $sample) {
            $html .= '<h3>'.e($sample['title']).'</h3>';
            if ($sample['description'] !== null) {
                $html .= '<p>'.e($sample['description']).'</p>';
            }
            $html .= '<pre><code class="language-mermaid">'.e(trim($sample['source'])).'</code></pre>';
        }

        return ['document' => $html];
    }

    protected function demo(): array
    {
        return [
            Layout::rows([
                Wysiwyg::make('document')
                    ->title(__('Document'))
                    ->preset('full')
                    ->height(720),
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

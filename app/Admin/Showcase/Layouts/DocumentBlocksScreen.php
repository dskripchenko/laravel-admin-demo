<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\Wysiwyg;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Models and boards in text: fenced ```bpmn, ```dmn, ```drawio,
 * ```kanban and ```calendar blocks inside markdown are drawn as the model,
 * the picture, the board and the calendar. In the WYSIWYG editor the same
 * blocks live in the stored HTML; the board and the calendar are edited
 * right in the block, and the slash menu inserts them (/kanban, /calendar,
 * /flow, /asyncapi).
 */
final class DocumentBlocksScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-document-blocks';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'blocks';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Models and boards in text');
    }

    public function description(): ?string
    {
        return __('BPMN, DMN, draw.io, kanban and calendar blocks in markdown and in the WYSIWYG editor.');
    }

    public function query(mixed ...$params): array
    {
        $html = '<h2>'.e(__('A planning note')).'</h2>'
            .'<p>'.e(__('The board and the calendar below are blocks of this document: move a card or an event right here, the change goes into the HTML. Type /kanban, /calendar, /flow or /asyncapi on an empty line to add another block.')).'</p>';
        foreach (self::blocks() as $block) {
            $html .= '<h3>'.e($block['title']).'</h3>'
                .'<pre><code class="language-'.$block['language'].'">'.e(trim($block['source'])).'</code></pre>';
        }

        return ['note' => $html];
    }

    protected function demo(): array
    {
        $fence = '```';
        $markdown = ['## '.__('Blocks in markdown'), __('Each block is a fenced code block with its language; the panel draws it read-only.')];
        foreach (self::blocks() as $block) {
            $markdown[] = '### '.$block['title'];
            $markdown[] = "{$fence}{$block['language']}\n".trim($block['source'])."\n{$fence}";
        }

        return [
            Layout::tabs([
                __('Rendered content') => [
                    Layout::markdown(implode("\n\n", $markdown))->toc()->tocLabel(__('Blocks on this card'))->card(),
                ],
                __('In the editor') => [
                    Wysiwyg::make('note')
                        ->title(__('Document'))
                        ->preset('full')
                        ->height(720)
                        ->help(__('Type /kanban, /calendar, /flow or /asyncapi on an empty line to insert a block.')),
                ],
            ]),
        ];
    }

    /**
     * The blocks shown on both tabs.
     *
     * @return list<array{title: string, language: string, source: string}>
     */
    private static function blocks(): array
    {
        return [
            ['title' => __('Kanban'), 'language' => 'kanban', 'source' => DocumentSamples::kanban()],
            ['title' => __('Calendar'), 'language' => 'calendar', 'source' => DocumentSamples::calendar()],
            ['title' => __('BPMN process'), 'language' => 'bpmn', 'source' => DocumentSamples::file('order.bpmn')],
            ['title' => __('DMN decisions'), 'language' => 'dmn', 'source' => DocumentSamples::file('invoice.dmn')],
            ['title' => 'draw.io', 'language' => 'drawio', 'source' => DocumentSamples::file('architecture.drawio')],
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

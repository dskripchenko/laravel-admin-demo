<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Documents: a diagram in any notation, a BPMN process, DMN
 * rules, a draw.io file and an AsyncAPI reference, each drawn in the browser
 * from its source text. The source is a key of the screen's state here — the
 * files are read in query() — but it can also be the text itself or url().
 */
final class DocumentsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-documents';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'file-code';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Documents');
    }

    public function description(): ?string
    {
        return __('Layout::diagram() in seven notations, Layout::bpmn(), dmn(), drawio() and asyncapi().');
    }

    public function query(mixed ...$params): array
    {
        return [
            'process' => DocumentSamples::file('order.bpmn'),
            'rules' => DocumentSamples::file('invoice.dmn'),
            'architecture' => DocumentSamples::file('architecture.drawio'),
            'events' => DocumentSamples::file('order-events.yaml'),
        ];
    }

    protected function demo(): array
    {
        // One diagram per notation; the Mermaid one is inline text.
        $diagrams = [
            'Mermaid' => [
                Layout::diagram("flowchart LR\n  cart([Cart]) --> pay{Paid?}\n  pay -->|yes| ship[Ship]\n  pay -->|no| cancel[Cancel]")
                    ->syntax('mermaid'),
            ],
        ];
        foreach (DocumentSamples::diagrams() as $sample) {
            $diagrams[$sample['title']] = [
                Layout::markdown($sample['description']),
                Layout::diagram($sample['source'])->syntax($sample['syntax'])->height(480),
            ];
        }

        return [
            Layout::block(__('Diagrams from text'), [Layout::tabs($diagrams)])
                ->icon('workflow')
                ->description(__('syntax() names the notation; without it the package tells it from the first line.')),

            Layout::block(__('Processes and decisions'), [
                Layout::bpmn('process')
                    ->title(__('Order handling (BPMN)'))
                    ->highlight(['Task_Pack'])
                    ->height(460),
                Layout::dmn('rules')
                    ->title(__('Pay by invoice (DMN): double-click a decision for its table'))
                    ->height(420),
            ])->icon('layers')
                ->description(__('Read-only, with pan and zoom; highlight() outlines the current step.')),

            Layout::block(__('Architecture and events'), [
                Layout::tabs([
                    'draw.io' => [Layout::drawio('architecture')->page('Architecture')->height(520)],
                    'AsyncAPI' => [Layout::asyncapi('events')],
                ]),
            ])->icon('server'),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

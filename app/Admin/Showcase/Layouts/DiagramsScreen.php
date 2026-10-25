<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Diagrams: fenced ```mermaid blocks in any markdown become
 * diagrams — flowchart, sequence, state, ER, class, C4, pie, gantt, timeline,
 * XY chart and mind map — drawn as SVG in the
 * panel's colours, in both themes. A block that cannot be read shows as code
 * with the reason and the line.
 */
final class DiagramsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-diagrams';
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
        return 'Diagrams';
    }

    public function description(): ?string
    {
        return __('Mermaid flowcharts, sequence, state, ER, class, C4, pie, gantt, timeline, XY chart and mind map diagrams inside Layout::markdown().');
    }

    protected function demo(): array
    {
        $fence = '```';
        $blocks = [];
        foreach (DiagramSamples::all() as $sample) {
            $blocks[] = '## '.$sample['title'];
            if ($sample['description'] !== null) {
                $blocks[] = $sample['description'];
            }
            $blocks[] = "{$fence}mermaid\n".trim($sample['source'])."\n{$fence}";
        }
        $blocks[] = '## '.__('A block that cannot be read');
        $blocks[] = __('The block below has a broken edge: it is shown as code with the reason and the line.');
        $blocks[] = "{$fence}mermaid\nflowchart LR\n    A -> B\n{$fence}";

        return [
            Layout::markdown(implode("\n\n", $blocks))->toc()->tocLabel(__('Diagrams on this card'))->card(),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

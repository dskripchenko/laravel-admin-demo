<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Diagrams: fenced ```mermaid blocks in any markdown become
 * diagrams — flowchart, sequence, state, pie and gantt — drawn as SVG in the
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
        return 'Mermaid flowcharts, sequence, state, pie and gantt diagrams inside Layout::markdown().';
    }

    protected function demo(): array
    {
        $fence = '```';
        $mermaid = fn (string $source): string => "{$fence}mermaid\n".trim($source)."\n{$fence}";

        return [
            Layout::markdown(implode("\n\n", [
                '## '.__('Order flow'),
                __('A flowchart with a subgraph, a decision and a loop back.'),
                $mermaid(__(<<<'MMD'
                    flowchart LR
                        cart([Cart]) --> checkout[Checkout]
                        checkout --> pay{Payment OK?}
                        pay -->|yes| fulfil
                        pay -->|no| retry[Ask for another card]
                        retry --> checkout
                        subgraph fulfil [Fulfilment]
                            direction TB
                            pick[Pick items] --> pack[Pack] --> ship[(Ship)]
                        end
                        fulfil --> done([Delivered])
                    MMD)),

                '## '.__('Publishing a post'),
                __('A sequence diagram with activations, a loop and alternatives.'),
                $mermaid(__(<<<'MMD'
                    sequenceDiagram
                        autonumber
                        actor A as Author
                        participant P as Panel
                        participant Q as Queue
                        A->>+P: Save draft
                        P-->>-A: Saved
                        A->>+P: Publish
                        P->>Q: Render preview
                        loop Every 5 s
                            P->>Q: Status?
                        end
                        alt Rendered
                            Q-->>P: Done
                            P-->>A: Published
                        else Failed
                            Q-->>P: Error
                            P-->>-A: Try again later
                        end
                    MMD)),

                '## '.__('Order states'),
                $mermaid(__(<<<'MMD'
                    stateDiagram-v2
                        [*] --> New
                        New --> Paid : payment
                        New --> Cancelled : timeout
                        state Paid {
                            [*] --> Packing
                            Packing --> Shipped
                            Shipped --> [*]
                        }
                        Paid --> Delivered
                        Delivered --> [*]
                        Cancelled --> [*]
                        note right of New : Unpaid orders expire in 24 h
                    MMD)),

                '## '.__('Sales by category'),
                $mermaid(__(<<<'MMD'
                    pie showData
                        "Books" : 412
                        "Electronics" : 268
                        "Home" : 154
                        "Toys" : 73
                    MMD)),

                '## '.__('Release plan'),
                $mermaid(__(<<<'MMD'
                    gantt
                        dateFormat YYYY-MM-DD
                        axisFormat %d %b
                        excludes weekends
                        section Design
                        Research      :done, r, 2026-11-02, 4d
                        Mock-ups      :done, m, after r, 3d
                        section Build
                        Backend       :crit, b, after m, 8d
                        Frontend      :active, f, after m, 10d
                        section Launch
                        Release       :milestone, after b f, 0d
                    MMD)),

                '## '.__('A block that cannot be read'),
                __('The block below has a broken edge: it is shown as code with the reason and the line.'),
                $mermaid("flowchart LR\n    A -> B"),
            ]))->toc()->tocLabel(__('Diagrams on this card'))->card(),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

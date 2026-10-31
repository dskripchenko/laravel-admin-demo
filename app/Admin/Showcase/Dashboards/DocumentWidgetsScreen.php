<?php

namespace App\Admin\Showcase\Dashboards;

use App\Admin\Resources\Shop\OrderResource;
use App\Admin\Showcase\Layouts\DocumentSamples;
use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Widget\BpmnWidget;
use Dskripchenko\LaravelAdmin\Widget\CalendarWidget;
use Dskripchenko\LaravelAdmin\Widget\DiagramWidget;
use Dskripchenko\LaravelAdmin\Widget\KanbanWidget;

/**
 * Dashboards › Boards, calendars and diagrams: a resource's kanban board and
 * calendar on a dashboard — the orders, through the resource's own
 * endpoints, filters and permissions — a calendar written as markup, a
 * diagram and a BPMN process.
 */
final class DocumentWidgetsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-dashboards-documents';
    }

    public static function group(): string
    {
        return 'dashboards';
    }

    public static function icon(): string
    {
        return 'layout-grid';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('Boards, calendars and diagrams');
    }

    public function description(): ?string
    {
        return __('KanbanWidget and CalendarWidget over a resource or markup, DiagramWidget and BpmnWidget.');
    }

    protected function demo(): array
    {
        return [
            Layout::dashboard([
                // The kanban view of OrderResource in a widget, with a preset
                // filter of its list: the orders paid by invoice. Hidden from a
                // user who may not view orders; cards are dragged only by a
                // user who may update them, along the order flow.
                KanbanWidget::make()->title('Orders paid by invoice')->size(12)->rowSpan(4)
                    ->resource(OrderResource::class)
                    ->filters(['payment_method' => 'invoice'])
                    ->editable(),

                // The resource's calendar view: orders by the day they were placed.
                CalendarWidget::make()->title('Orders by day')->size(6)->rowSpan(4)
                    ->resource(OrderResource::class)
                    ->initialView('month'),

                // A calendar written as markup, read-only.
                CalendarWidget::make()->withSlug('shop-calendar')->title('Coming up')->size(6)->rowSpan(4)
                    ->markup(fn () => DocumentSamples::calendar())
                    ->initialView('agenda')
                    ->views(['agenda', 'month']),

                // A diagram in any notation the diagrams package reads.
                DiagramWidget::make()->title('Order states')->size(6)->rowSpan(3)
                    ->syntax('mermaid')
                    ->content("stateDiagram-v2\n  [*] --> pending\n  pending --> paid\n  pending --> cancelled\n  paid --> shipped\n  paid --> refunded\n  shipped --> delivered\n  delivered --> refunded"),

                // A BPMN process, read-only, with the current step outlined.
                BpmnWidget::make()->title('Order handling')->size(6)->rowSpan(3)
                    ->xml(fn () => DocumentSamples::file('order.bpmn'))
                    ->highlight(['Task_Pack']),
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }
}

<?php

namespace App\Admin\Showcase\Layouts;

use Illuminate\Support\Carbon;

/**
 * The documents of the showcase: a diagram in every notation besides
 * Mermaid, the shop's BPMN process, DMN rules, draw.io architecture and
 * AsyncAPI events (files in resources/showcase), and a kanban board and a
 * calendar written as markup. Shared by the layouts, the fields, the
 * markdown blocks and the dashboard widgets.
 */
final class DocumentSamples
{
    /**
     * One small diagram per notation, about the shop and the blog.
     *
     * @return list<array{syntax: string, title: string, description: string, source: string}>
     */
    public static function diagrams(): array
    {
        return [
            [
                'syntax' => 'plantuml',
                'title' => 'PlantUML',
                'description' => __('A sequence diagram: paying for an order.'),
                'source' => <<<'PUML'
                    @startuml
                    actor Customer
                    participant "Shop" as Shop
                    participant "Payment provider" as PSP
                    database "Orders" as DB
                    Customer -> Shop ++ : Pay for ORD-10042
                    Shop -> PSP ++ : Charge $129.00
                    PSP --> Shop -- : Approved
                    Shop -> DB : status = paid
                    Shop --> Customer -- : Receipt
                    @enduml
                    PUML,
            ],
            [
                'syntax' => 'dot',
                'title' => 'Graphviz DOT',
                'description' => __('A directed graph: how an order moves through the warehouse.'),
                'source' => <<<'DOT'
                    digraph fulfilment {
                      rankdir=LR
                      node [shape=box, style=rounded]
                      subgraph cluster_wh { label="Warehouse"; pick; pack }
                      paid [label="Paid"]
                      pick [label="Pick items"]
                      pack [label="Pack"]
                      ship [label="Hand to courier", shape=cylinder]
                      paid -> pick -> pack -> ship
                      pack -> pick [label="item missing", style=dashed]
                    }
                    DOT,
            ],
            [
                'syntax' => 'd2',
                'title' => 'D2',
                'description' => __('Containers and shapes: the storefront and its services.'),
                'source' => <<<'D2'
                    direction: right
                    customer: Customer { shape: person }
                    shop: Shop {
                      web: Storefront
                      api: Orders API
                      db: Orders DB { shape: cylinder }
                      web -> api: JSON
                      api -> db: SQL
                    }
                    psp: Payment provider { shape: cloud }
                    customer -> shop.web: HTTPS
                    shop.api -> psp: charge
                    D2,
            ],
            [
                'syntax' => 'dbml',
                'title' => 'DBML',
                'description' => __('A database schema: the tables behind the demo shop.'),
                'source' => <<<'DBML'
                    Table customers {
                      id integer [pk, increment]
                      name varchar [not null]
                      email varchar [unique]
                      segment customer_segment
                    }
                    Table orders {
                      id integer [pk, increment]
                      number varchar [unique, note: 'ORD-10001']
                      customer_id integer [ref: > customers.id]
                      status order_status
                      total decimal
                    }
                    Table order_items {
                      id integer [pk]
                      order_id integer [ref: > orders.id]
                      product_id integer [ref: > products.id]
                      quantity integer
                    }
                    Table products {
                      id integer [pk]
                      sku varchar [unique]
                      price decimal
                    }
                    Enum order_status {
                      pending
                      paid
                      shipped
                      delivered
                    }
                    DBML,
            ],
            [
                'syntax' => 'structurizr',
                'title' => 'Structurizr',
                'description' => __('A C4 container view written in the Structurizr DSL.'),
                'source' => <<<'DSL'
                    workspace {
                      model {
                        customer = person "Customer"
                        editor = person "Editor"
                        shop = softwareSystem "Shop" {
                          web = container "Storefront" "Catalog and checkout" "Vue"
                          admin = container "Admin panel" "Orders, products, blog" "Laravel"
                          db = container "Database" "" "SQLite" "Database"
                        }
                        psp = softwareSystem "Payments" "" "External"
                        customer -> web "Buys"
                        editor -> admin "Manages"
                        web -> db "Reads and writes"
                        admin -> db "Reads and writes"
                        web -> psp "Charges"
                      }
                      views {
                        container shop {
                          include *
                          autolayout lr
                        }
                      }
                    }
                    DSL,
            ],
            [
                'syntax' => 'wardley',
                'title' => 'Wardley map',
                'description' => __('What the shop builds and what it buys.'),
                'source' => <<<'WARDLEY'
                    title Online shop
                    anchor Customer [0.95, 0.6]
                    component Checkout [0.8, 0.55]
                    component Catalog [0.7, 0.45]
                    component Admin panel [0.55, 0.35] (build)
                    component Payments [0.45, 0.85] (buy)
                    component Hosting [0.15, 0.9] (outsource)
                    evolve Admin panel 0.6
                    Customer->Checkout
                    Customer->Catalog
                    Checkout->Payments
                    Catalog->Admin panel
                    Admin panel->Hosting
                    Payments->Hosting
                    WARDLEY,
            ],
        ];
    }

    /** A file of resources/showcase: order.bpmn, invoice.dmn, architecture.drawio, order-events.yaml. */
    public static function file(string $name): string
    {
        return (string) file_get_contents(resource_path('showcase/'.$name));
    }

    /** The launch of a sale as a kanban board: columns, tags, people, due dates, a WIP limit. */
    public static function kanban(): string
    {
        $due = fn (int $days) => Carbon::today()->addDays($days)->toDateString();

        return <<<MD
            # Black Friday launch
            tags: catalog=info, marketing=warning, warehouse=success, bug=danger

            ## Backlog
            - Pick the 40 discounted products #catalog {who: Eddie Editor, due: {$due(9)}}
            - Banner for the home page #marketing
              Two sizes: 1920×480 and a mobile one.
            - Gift wrapping as an option at checkout #catalog !low

            ## In progress {wip: 3}
            - Newsletter to the VIP segment #marketing {who: Vera Viewer, due: {$due(4)}}
            - Extra packing station #warehouse !high {who: Ada Admin, due: {$due(6)}}

            ## Review
            - Discount codes do not stack #bug !high {who: Ada Admin}

            ## Done {done}
            - Courier contract for the peak #warehouse
            - Sale landing page #marketing
            MD;
    }

    /** The shop's month as calendar markup: single events, a range and repeats. */
    public static function calendar(): string
    {
        $day = fn (int $days) => Carbon::today()->addDays($days)->toDateString();
        $from = Carbon::today()->startOfMonth()->toDateString();

        return <<<MD
            # Shop calendar
            tags: sale=danger, stock=success, team=info

            {$day(1)} 10:00-11:00 Supplier call: winter catalog #stock {where: Room 2}
            {$day(3)} Price list update #stock
            {$day(5)}..{$day(7)} Weekend sale #sale {description: Up to **30%** off the home category.}
            {$day(10)} 14:00-15:30 Photo shoot for new arrivals #team
            every mon 10:00-10:30 Sales review #team {from: {$from}}
            every 2 weeks thu 09:00-09:30 Newsletter goes out #sale {from: {$from}}
            every month last fri Stocktaking #stock
            MD;
    }
}

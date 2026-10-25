<?php

namespace App\Admin\Showcase\Layouts;

/**
 * The diagram samples of the showcase: one of every type the panel draws,
 * shared by the Markdown page and the WYSIWYG page.
 */
final class DiagramSamples
{
    /**
     * @return list<array{key: string, title: string, description: string|null, source: string}>
     */
    public static function all(): array
    {
        return [
            [
                'key' => 'flowchart',
                'title' => __('Order flow'),
                'description' => __('A flowchart with a subgraph, a decision and a loop back.'),
                'source' => __(<<<'MMD'
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
                MMD),
            ],
            [
                'key' => 'sequence',
                'title' => __('Publishing a post'),
                'description' => __('A sequence diagram with activations, a loop and alternatives.'),
                'source' => __(<<<'MMD'
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
                MMD),
            ],
            [
                'key' => 'state',
                'title' => __('Order states'),
                'description' => null,
                'source' => __(<<<'MMD'
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
                MMD),
            ],
            [
                'key' => 'pie',
                'title' => __('Sales by category'),
                'description' => null,
                'source' => __(<<<'MMD'
                pie showData
                    "Books" : 412
                    "Electronics" : 268
                    "Home" : 154
                    "Toys" : 73
                MMD),
            ],
            [
                'key' => 'gantt',
                'title' => __('Release plan'),
                'description' => null,
                'source' => __(<<<'MMD'
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
                MMD),
            ],
            [
                'key' => 'er',
                'title' => __('Data model'),
                'description' => __('An ER diagram: entities with their keys and the cardinality of every relationship.'),
                'source' => __(<<<'MMD'
                erDiagram
                    CUSTOMER ||--o{ ORDER : places
                    ORDER ||--|{ LINE_ITEM : contains
                    PRODUCT ||--o{ LINE_ITEM : "ordered as"
                    CUSTOMER {
                        int id PK
                        string email UK "login"
                        string name
                    }
                    ORDER {
                        int id PK
                        int customer_id FK
                        decimal total
                        string status "new, paid or shipped"
                    }
                    LINE_ITEM {
                        int order_id PK, FK
                        string sku PK, FK
                        int quantity
                    }
                    PRODUCT {
                        string sku PK
                        string title
                        decimal price
                    }
                MMD),
            ],
            [
                'key' => 'class',
                'title' => __('Class structure'),
                'description' => __('A class diagram: an interface, an implementation, composition and an enumeration.'),
                'source' => __(<<<'MMD'
                classDiagram
                    direction TB
                    class PaymentGateway {
                        <<interface>>
                        +charge(Order order) Receipt
                        +refund(Receipt receipt) bool
                    }
                    class CardGateway {
                        -string apiKey
                        +charge(Order order) Receipt
                        +refund(Receipt receipt) bool
                    }
                    class Order {
                        +int id
                        +decimal total
                        +Status status
                        +lines() List~Line~
                    }
                    class Status {
                        <<enumeration>>
                        NEW
                        PAID
                        SHIPPED
                    }
                    PaymentGateway <|.. CardGateway : implements
                    Order "1" *-- "1..*" Line : contains
                    Order --> Status
                    CardGateway ..> Order : charges
                MMD),
            ],
            [
                'key' => 'c4',
                'title' => __('System architecture'),
                'description' => __('A C4 container diagram: a person, the containers of a system inside its boundary and the external systems it talks to.'),
                'source' => __(<<<'MMD'
                C4Container
                    title The shop
                    Person(customer, "Customer", "Buys goods online")
                    System_Boundary(shop, "Shop") {
                        Container(web, "Storefront", "Vue", "Catalog, cart and checkout")
                        Container(api, "API", "Laravel", "Orders, payments, stock")
                        ContainerDb(db, "Database", "PostgreSQL", "Orders and products")
                    }
                    System_Ext(pay, "Payment provider", "Charges cards")
                    Rel(customer, web, "Uses", "HTTPS")
                    Rel(web, api, "Calls", "JSON")
                    Rel(api, db, "Reads and writes", "SQL")
                    Rel(api, pay, "Charges", "HTTPS")
                MMD),
            ],
            [
                'key' => 'timeline',
                'title' => __('Project history'),
                'description' => __('A timeline with sections and several events in one period.'),
                'source' => __(<<<'MMD'
                timeline
                    title The panel's history
                    section Foundations
                        2024 : First release : Resources and forms
                        2025 : Dashboards : Two-factor sign-in
                    section Platform
                        2026 Q2 : Multi-panel hosts
                        2026 Q4 : Diagrams : API blocks : WYSIWYG widgets
                MMD),
            ],
            [
                'key' => 'xychart',
                'title' => __('Monthly orders'),
                'description' => __('An XY chart: bars and a line on the same axes.'),
                'source' => __(<<<'MMD'
                xychart-beta
                    title "Orders per month"
                    x-axis [Jan, Feb, Mar, Apr, May, Jun]
                    y-axis "Orders" 0 --> 1200
                    bar [420, 510, 640, 700, 860, 990]
                    line [400, 480, 600, 720, 840, 960]
                MMD),
            ],
            [
                'key' => 'mindmap',
                'title' => __('Launch plan'),
                'description' => __('A mind map: the root in the middle, its branches on both sides.'),
                'source' => __(<<<'MMD'
                mindmap
                  root((Launch))
                    Product
                      Pricing
                      Docs
                    Marketing
                      Landing page
                      Newsletter
                    Support
                      FAQ
                      On-call rota
                    Legal
                      Terms
                      Privacy policy
                MMD),
            ],
        ];
    }
}

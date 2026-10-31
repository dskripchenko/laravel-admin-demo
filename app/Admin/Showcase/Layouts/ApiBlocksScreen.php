<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\Wysiwyg;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › API blocks: fenced ```openapi blocks — a request described in
 * YAML or JSON — become live API widgets: the operation with try-it, code
 * samples, TypeScript types and PDF. In the WYSIWYG editor the same block is
 * edited in place: a preview, the YAML/JSON source and a request constructor;
 * `/api` inserts one. A ```asyncapi block draws the reference of an AsyncAPI
 * document; `/asyncapi` inserts one.
 */
final class ApiBlocksScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-api-blocks';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'braces';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return __('API blocks');
    }

    public function description(): ?string
    {
        return __('Requests described in YAML or JSON, and AsyncAPI documents, inside markdown and the WYSIWYG editor become live API widgets.');
    }

    public function query(mixed ...$params): array
    {
        $fence = '<pre><code class="language-openapi">';

        return [
            'article' => '<h2>'.e(__('Creating an order')).'</h2>'
                .'<p>'.e(__('The block below is an API request: open the Constructor or Source tab to change it, or type /api on an empty line to add another one.')).'</p>'
                .$fence.e(self::ORDER_BLOCK).'</code></pre>'
                .'<p>'.e(__('The text around the block stays ordinary rich text.')).'</p>'
                .'<h2>'.e(__('Order events')).'</h2>'
                .'<p>'.e(__('An AsyncAPI block: type /asyncapi on an empty line to add one.')).'</p>'
                .'<pre><code class="language-asyncapi">'.e(trim(DocumentSamples::file('order-events.yaml'))).'</code></pre>',
        ];
    }

    protected function demo(): array
    {
        $fence = '```';

        return [
            Layout::tabs([
                __('Rendered content') => [
                    Layout::markdown(implode("\n\n", [
                        '## '.__('A live request to this panel'),
                        __('The block is a GET request to this demo\'s own API: press Send in Try it — the request goes from your browser and the response shows below it.'),
                        "{$fence}openapi\n".self::BOOTSTRAP_BLOCK."\n{$fence}",
                        '## '.__('Several requests in one block'),
                        __('A list of requests with shared authentication: each one gets its card, with code samples in thirteen languages, TypeScript types and a PDF of the operation.'),
                        "{$fence}openapi\n".self::CATALOG_BLOCK."\n{$fence}",
                        '## '.__('Events: an AsyncAPI block'),
                        __('An `asyncapi` block holds an AsyncAPI 2.x or 3.0 document, YAML or JSON: servers, channels, operations and messages with their payloads.'),
                        "{$fence}asyncapi\n".trim(DocumentSamples::file('order-events.yaml'))."\n{$fence}",
                    ]))->card(),
                ],
                __('In the editor') => [
                    Wysiwyg::make('article')
                        ->title(__('Article'))
                        ->preset('full')
                        ->height(640)
                        ->help(__('Type /api or /asyncapi on an empty line to insert an API block.')),
                ],
            ]),
        ];
    }

    protected function sourceMethod(): ?string
    {
        return 'demo';
    }

    private const BOOTSTRAP_BLOCK = <<<'YAML'
        method: GET
        url: /api/admin/system/bootstrap
        summary: The panel's bootstrap payload
        description: What the panel's SPA reads first — the CSRF token, the locale, the theme, the brand and the signed-in user.
        responses:
          200:
            description: The payload
            example:
              success: true
              payload: { locale: en, theme: light, brand: { name: Laravel Admin } }
        YAML;

    private const CATALOG_BLOCK = <<<'YAML'
        title: Catalog API
        baseUrl: https://api.example.com/v1
        auth: bearer
        requests:
          - method: GET
            url: /products
            summary: List products
            params:
              query:
                page: integer Page number, from 1
                category: string Category slug
            responses:
              200:
                description: A page of products
                example: { data: [{ id: 1, title: Lamp, price: 1290 }], total: 1 }
          - method: POST
            url: /products
            summary: Create a product
            body:
              example: { title: Lamp, price: 1290, tags: [home, light] }
            responses:
              201:
                description: The created product
                example: { id: 2, title: Lamp, price: 1290 }
              422: Validation failed
        YAML;

    private const ORDER_BLOCK = <<<'YAML'
        method: POST
        url: https://api.example.com/v1/orders
        summary: Create an order
        auth: bearer
        body:
          example:
            customer_id: 42
            items: [{ sku: LAMP-01, quantity: 2 }]
        responses:
          201:
            description: The order
            example: { id: 1001, status: new, total: 2580 }
          422: Validation failed
        YAML;
}

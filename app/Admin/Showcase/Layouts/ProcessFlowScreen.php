<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Flow;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\ValidationRulesExporter;
use Dskripchenko\LaravelAdmin\Infolist\FlowEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Resource\ActionFailedException;
use Illuminate\Support\Facades\Cache;

/**
 * Layouts › Process flow: an order's state machine drawn by Layout::flow.
 *
 * - The view polls `orderState()` every three seconds. The orders here are
 *   fake: each walks its own route through the machine as time passes, so
 *   the active step moves, the path lights up and counters change.
 * - Pick an order in the select to follow it, or "All orders" for the
 *   counters of every state.
 * - Hover a step or a transition and switch it off: `toggleStep()` stores the
 *   change. "Cancelled" refuses — the switch flips back with the reason.
 * - "Payment" is a sub-process held by reference: double-click it,
 *   `loadSubprocess()` returns its graph and the polling follows you in.
 * - The drawer of a step has a "Retry step" button bound to `retryStep()`.
 * - The Editor tab is Field\Flow: draw a process, save it with the form; the
 *   model is checked on the server; FlowEntry below draws it read-only.
 * - The last tab is a ```flow block in markdown.
 */
final class ProcessFlowScreen extends ShowcaseScreen
{
    /** How long a fake order stays in one step, in seconds. */
    private const STEP_SECONDS = 3;

    /**
     * The routes the fake orders take, step by step. The keys are not
     * numeric on purpose: PHP would turn '1001' into an integer key, and the
     * select's value would no longer match the state.
     */
    private const ROUTES = [
        'ORD-1001' => ['start', 'new', 'validate', 'payment', 'paid', 'picking', 'shipped', 'delivered', 'completed'],
        'ORD-1002' => ['start', 'new', 'validate', 'payment', 'paid', 'picking', 'shipped', 'delivery_failed', 'shipped', 'delivered', 'completed'],
        'ORD-1003' => ['start', 'new', 'validate', 'rejected'],
        'ORD-1004' => ['start', 'new', 'validate', 'payment', 'paid', 'cancelled', 'closed'],
    ];

    /** The payment sub-process, step by step, while an order is in it. */
    private const PAYMENT_ROUTE = ['p_start', 'authorize', 'secure', 'challenge', 'capture', 'p_end'];

    public static function slug(): string
    {
        return 'showcase-layouts-process-flow';
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
        return __('Process flow');
    }

    public function description(): ?string
    {
        return __('A state machine with live monitoring, switches and a sub-process — and the same model edited as a form field.');
    }

    public function query(mixed ...$params): array
    {
        return [
            'order' => 'ORD-1002',
            'process' => $this->orderProcess(),
            'workflow' => Cache::get(self::cacheKey('workflow'), self::sampleWorkflow()),
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::tabs([
                __('Monitoring') => [
                    Layout::rows([
                        Select::make('order')->title(__('Order'))->options([
                            '' => __('All orders'),
                            'ORD-1001' => '#1001 — '.__('delivered'),
                            'ORD-1002' => '#1002 — '.__('delivery retried'),
                            'ORD-1003' => '#1003 — '.__('rejected'),
                            'ORD-1004' => '#1004 — '.__('cancelled after payment'),
                        ])->span(6),
                    ]),
                    Layout::flow('process')
                        ->poll('orderState', seconds: 3)
                        ->onToggle('toggleStep')
                        ->subgraphs('loadSubprocess')
                        ->instanceFrom('order')
                        ->actions([
                            Button::make(__('Retry step'))->method('retryStep')->icon('rotate-ccw'),
                        ])
                        ->height(560),
                ],
                __('Editor') => [
                    Layout::rows($this->editorFields()),
                    Layout::infolist([
                        FlowEntry::make('workflow')->label(__('The same model, read-only (FlowEntry)'))->height(320),
                    ]),
                ],
                __('In markdown') => [
                    Layout::markdown(implode("\n\n", [
                        __('A fenced `flow` block — a JSON model, or Mermaid state-diagram source — becomes a read-only process view in any markdown of the panel:'),
                        "```flow\nstateDiagram-v2\n  [*] --> Draft\n  Draft --> Review : submit\n  Review --> Draft : changes requested\n  Review --> Published : approve\n  Published --> [*]\n```",
                    ]))->card(),
                ],
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make(__('Save the process'))->method('save')->primary()->icon('save')];
    }

    /* ---------------- the methods the view calls ---------------- */

    /**
     * The monitoring snapshot for the level on screen: the root, or the
     * payment sub-process (`$context['path'] === ['payment']`).
     *
     * @param  array{path?: list<string>, ref?: ?string, instance?: mixed}  $context
     * @return array<string, mixed>
     */
    public function orderState(array $context): array
    {
        $instance = (string) ($context['instance'] ?? '');
        $inPayment = ($context['path'] ?? []) === ['payment'];

        if ($instance === '' || ! isset(self::ROUTES[$instance])) {
            return $inPayment ? $this->paymentOverview() : $this->overview();
        }

        [$route, $at] = $this->position($instance);
        $current = $route[$at];

        if ($inPayment) {
            return $this->paymentState($route, $at);
        }

        $nodes = [];
        foreach (array_slice($route, 0, $at) as $done) {
            $nodes[$done] = ['status' => 'done'];
        }
        $nodes[$current] = match ($current) {
            'delivery_failed' => ['status' => 'error', 'message' => __('Carrier API timeout, resending'), 'badge' => 1],
            'rejected', 'cancelled' => ['status' => 'skipped'],
            'completed', 'closed' => ['status' => 'done'],
            default => ['status' => 'active', 'updatedAt' => now()->toIso8601String()],
        };

        return [
            'nodes' => $nodes,
            'path' => array_slice($route, 0, $at + 1),
            'current' => [$current],
            'updatedAt' => now()->toIso8601String(),
        ];
    }

    /**
     * Persists a switch. The cancellation step is required by consumer law,
     * so turning it off is refused — the switch flips back with this text.
     *
     * @param  array{kind: string, id: string, enabled: bool, path: list<string>}  $change
     */
    public function toggleStep(array $change): void
    {
        if ($change['id'] === 'cancelled' && ! $change['enabled']) {
            throw new ActionFailedException(__('Cancellation cannot be switched off: customers may cancel until shipping.'));
        }

        $disabled = Cache::get(self::cacheKey('disabled'), []);
        $key = $change['kind'].':'.implode('/', [...$change['path'], $change['id']]);
        if ($change['enabled']) {
            unset($disabled[$key]);
        } else {
            $disabled[$key] = true;
        }
        Cache::put(self::cacheKey('disabled'), $disabled, now()->addDay());
    }

    /**
     * A sub-process held by reference.
     *
     * @param  array<string, mixed>  $node
     * @return array<string, mixed>
     */
    public function loadSubprocess(string $ref, array $node): array
    {
        if ($ref !== 'payment') {
            throw new ActionFailedException(__('Unknown sub-process :ref', ['ref' => $ref]));
        }

        return $this->withDisabled(self::paymentProcess(), ['payment']);
    }

    /**
     * The drawer's button.
     *
     * @param  array{kind: string, id: string, path: list<string>, instance: mixed}  $selection
     * @return array<string, string>
     */
    public function retryStep(array $selection): array
    {
        $order = $selection['instance'] ? '#'.substr((string) $selection['instance'], 4) : __('every order');

        return ['message' => __('Step “:step” of :order is queued for a retry.', ['step' => $selection['id'], 'order' => $order])];
    }

    /**
     * Saves the editor's model, checked by the field's own rules.
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function save(array $state): array
    {
        $data = validator($state, ValidationRulesExporter::export($this->editorFields()))->validate();
        Cache::put(self::cacheKey('workflow'), $data['workflow'], now()->addDay());

        return [
            'message' => __('The process is saved: :n steps.', ['n' => count($data['workflow']['nodes'] ?? [])]),
            'state' => ['workflow' => $data['workflow']],
        ];
    }

    /* ---------------- the models ---------------- */

    /**
     * @return list<Flow>
     */
    private function editorFields(): array
    {
        return [
            Flow::make('workflow')
                ->title(__('Approval process'))
                ->help(__('Drag steps from the panel, connect them, double-click to rename. Save with the button above.'))
                ->palette(['start', 'state', 'task', 'decision', 'end', 'note'])
                ->locked(['start'])
                ->required()
                ->height(520),
        ];
    }

    /**
     * The order state machine, with the switches stored by toggleStep().
     *
     * @return array<string, mixed>
     */
    private function orderProcess(): array
    {
        return $this->withDisabled([
            'version' => 1,
            'meta' => ['title' => __('Order'), 'direction' => 'LR'],
            'nodes' => [
                ['id' => 'start', 'type' => 'start', 'label' => __('Placed')],
                ['id' => 'new', 'type' => 'state', 'label' => __('New'), 'description' => __('awaiting checks')],
                ['id' => 'validate', 'type' => 'decision', 'label' => __('Valid?')],
                ['id' => 'payment', 'type' => 'subprocess', 'label' => __('Payment'), 'subgraph' => 'payment',
                    'data' => ['provider' => 'Stripe', 'timeout' => '15 min']],
                ['id' => 'paid', 'type' => 'state', 'label' => __('Paid')],
                ['id' => 'picking', 'type' => 'task', 'label' => __('Picking'), 'description' => __('warehouse')],
                ['id' => 'shipped', 'type' => 'state', 'label' => __('Shipped')],
                ['id' => 'delivery_failed', 'type' => 'state', 'label' => __('Delivery failed')],
                ['id' => 'delivered', 'type' => 'state', 'label' => __('Delivered')],
                ['id' => 'rejected', 'type' => 'state', 'label' => __('Rejected')],
                ['id' => 'cancelled', 'type' => 'state', 'label' => __('Cancelled'), 'description' => __('refund issued')],
                ['id' => 'completed', 'type' => 'end', 'label' => __('Completed')],
                ['id' => 'closed', 'type' => 'end', 'label' => __('Closed')],
            ],
            'edges' => [
                ['id' => 'e_new', 'from' => 'start', 'to' => 'new'],
                ['id' => 'e_validate', 'from' => 'new', 'to' => 'validate', 'label' => __('check')],
                ['id' => 'e_ok', 'from' => 'validate', 'to' => 'payment', 'label' => __('yes')],
                ['id' => 'e_bad', 'from' => 'validate', 'to' => 'rejected', 'label' => __('no')],
                ['id' => 'e_paid', 'from' => 'payment', 'to' => 'paid'],
                ['id' => 'e_pick', 'from' => 'paid', 'to' => 'picking'],
                ['id' => 'e_ship', 'from' => 'picking', 'to' => 'shipped'],
                ['id' => 'e_deliver', 'from' => 'shipped', 'to' => 'delivered'],
                ['id' => 'e_fail', 'from' => 'shipped', 'to' => 'delivery_failed', 'condition' => 'carrier error'],
                ['id' => 'e_resend', 'from' => 'delivery_failed', 'to' => 'shipped', 'label' => __('resend')],
                ['id' => 'e_done', 'from' => 'delivered', 'to' => 'completed'],
                ['id' => 'e_cancel_new', 'from' => 'new', 'to' => 'cancelled', 'label' => __('cancel')],
                ['id' => 'e_cancel_paid', 'from' => 'paid', 'to' => 'cancelled', 'label' => __('refund')],
                ['id' => 'e_closed', 'from' => 'cancelled', 'to' => 'closed'],
                ['id' => 'e_rejected', 'from' => 'rejected', 'to' => 'closed'],
            ],
        ], []);
    }

    /**
     * @return array<string, mixed>
     */
    private static function paymentProcess(): array
    {
        return [
            'version' => 1,
            'meta' => ['title' => __('Payment'), 'direction' => 'LR'],
            'nodes' => [
                ['id' => 'p_start', 'type' => 'start'],
                ['id' => 'authorize', 'type' => 'task', 'label' => __('Authorize card')],
                ['id' => 'secure', 'type' => 'decision', 'label' => __('3-D Secure?')],
                ['id' => 'challenge', 'type' => 'task', 'label' => __('Challenge')],
                ['id' => 'capture', 'type' => 'task', 'label' => __('Capture')],
                ['id' => 'p_end', 'type' => 'end', 'label' => __('Paid')],
            ],
            'edges' => [
                ['id' => 'p1', 'from' => 'p_start', 'to' => 'authorize'],
                ['id' => 'p2', 'from' => 'authorize', 'to' => 'secure'],
                ['id' => 'p3', 'from' => 'secure', 'to' => 'challenge', 'label' => __('required')],
                ['id' => 'p4', 'from' => 'secure', 'to' => 'capture', 'label' => __('exempt')],
                ['id' => 'p5', 'from' => 'challenge', 'to' => 'capture'],
                ['id' => 'p6', 'from' => 'capture', 'to' => 'p_end'],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private static function sampleWorkflow(): array
    {
        return [
            'version' => 1,
            'nodes' => [
                ['id' => 'start', 'type' => 'start', 'label' => __('Request')],
                ['id' => 'review', 'type' => 'task', 'label' => __('Manager review')],
                ['id' => 'approved', 'type' => 'decision', 'label' => __('Approved?')],
                ['id' => 'done', 'type' => 'end', 'label' => __('Done')],
            ],
            'edges' => [
                ['id' => 'w1', 'from' => 'start', 'to' => 'review'],
                ['id' => 'w2', 'from' => 'review', 'to' => 'approved'],
                ['id' => 'w3', 'from' => 'approved', 'to' => 'done', 'label' => __('yes')],
                ['id' => 'w4', 'from' => 'approved', 'to' => 'review', 'label' => __('rework')],
            ],
        ];
    }

    /* ---------------- the fake orders ---------------- */

    /**
     * Where an order is now: its route and the index of its step. The route
     * loops, with a pause at the end.
     *
     * @return array{0: list<string>, 1: int}
     */
    private function position(string $instance): array
    {
        $route = self::ROUTES[$instance];
        $length = count($route) + 2;
        // Each order starts at its own offset, so they are never in step.
        $tick = intdiv(time(), self::STEP_SECONDS) + 3 * (int) array_search($instance, array_keys(self::ROUTES), true);

        return [$route, min($tick % $length, count($route) - 1)];
    }

    /**
     * The payment sub-process of one order: done once the order is past it.
     *
     * @param  list<string>  $route
     * @return array<string, mixed>
     */
    private function paymentState(array $route, int $at): array
    {
        $paymentAt = array_search('payment', $route, true);
        if ($paymentAt === false || $at < $paymentAt) {
            return ['nodes' => [], 'updatedAt' => now()->toIso8601String()];
        }
        $steps = $at > $paymentAt
            ? count(self::PAYMENT_ROUTE) - 1
            : time() % count(self::PAYMENT_ROUTE);
        $nodes = [];
        foreach (array_slice(self::PAYMENT_ROUTE, 0, $steps) as $done) {
            $nodes[$done] = ['status' => 'done'];
        }
        $current = self::PAYMENT_ROUTE[$steps];
        $nodes[$current] = ['status' => $current === 'p_end' ? 'done' : 'active'];

        return [
            'nodes' => $nodes,
            'path' => array_slice(self::PAYMENT_ROUTE, 0, $steps + 1),
            'current' => [$current],
            'updatedAt' => now()->toIso8601String(),
        ];
    }

    /**
     * Every order at once: how many are in each state.
     *
     * @return array<string, mixed>
     */
    private function overview(): array
    {
        $counts = [];
        foreach (array_keys(self::ROUTES) as $instance) {
            [$route, $at] = $this->position((string) $instance);
            $counts[$route[$at]] = ($counts[$route[$at]] ?? 0) + 1;
        }
        // A steady backlog makes the counters look like a real shop.
        $base = ['new' => 12, 'payment' => 4, 'picking' => 7, 'shipped' => 23, 'delivery_failed' => 2];
        $nodes = [];
        foreach ($base + $counts as $id => $_) {
            $count = ($base[$id] ?? 0) + ($counts[$id] ?? 0);
            $nodes[$id] = ['count' => $count, 'status' => $id === 'delivery_failed' ? 'error' : 'waiting'];
            if ($id === 'delivery_failed') {
                $nodes[$id]['badge'] = $count;
                $nodes[$id]['message'] = __('Carrier API timeouts');
            }
        }

        return ['nodes' => $nodes, 'edges' => ['e_deliver' => ['count' => 46], 'e_fail' => ['count' => 3]], 'updatedAt' => now()->toIso8601String()];
    }

    /**
     * @return array<string, mixed>
     */
    private function paymentOverview(): array
    {
        return ['nodes' => [
            'authorize' => ['count' => 2, 'status' => 'waiting'],
            'challenge' => ['count' => 1, 'status' => 'waiting'],
            'capture' => ['count' => 1, 'status' => 'active'],
        ], 'updatedAt' => now()->toIso8601String()];
    }

    /**
     * The model with the switches toggleStep() stored.
     *
     * @param  array<string, mixed>  $graph
     * @param  list<string>  $path
     * @return array<string, mixed>
     */
    private function withDisabled(array $graph, array $path): array
    {
        $disabled = Cache::get(self::cacheKey('disabled'), []);
        foreach (['node' => 'nodes', 'edge' => 'edges'] as $kind => $list) {
            foreach ($graph[$list] as $i => $element) {
                if (isset($disabled[$kind.':'.implode('/', [...$path, $element['id']])])) {
                    $graph[$list][$i]['enabled'] = false;
                }
            }
        }

        return $graph;
    }

    /** One copy per signed-in user, so visitors do not see each other's changes. */
    private static function cacheKey(string $what): string
    {
        return 'showcase-flow:'.$what.':'.(auth('admin')->id() ?? 'guest');
    }
}

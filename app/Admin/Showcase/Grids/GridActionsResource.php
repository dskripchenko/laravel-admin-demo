<?php

namespace App\Admin\Showcase\Grids;

use App\Enums\OrderStatus;
use App\Models\Shop\Order;
use Dskripchenko\LaravelAdmin\Action\BulkAction;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Action\DropDown;
use Dskripchenko\LaravelAdmin\Action\ModalAction;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Filter\OptionsFilter;
use Dskripchenko\LaravelAdmin\Resource\ActionFailedException;
use Dskripchenko\LaravelAdmin\Resource\Resource;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Database\Eloquent\Builder;

/**
 * Grids › Row and bulk actions over the orders. Every action calls a public
 * method of this resource with the ids of the rows it was run on.
 */
final class GridActionsResource extends Resource
{
    public static string $model = Order::class;

    public static string $icon = 'zap';

    public static function slug(): string
    {
        return 'showcase-grid-actions';
    }

    public static function label(): string
    {
        return 'Row and bulk actions';
    }

    /** One record's name, for titles, confirmations and toasts: "Create order". */
    public static function singularLabel(): ?string
    {
        return 'order';
    }

    public static function permission(): string
    {
        return 'admin.orders';
    }

    public function fields(): array
    {
        return [];
    }

    public function columns(): array
    {
        return [
            TableColumn::make('number')->search(),
            TableColumn::make('customer_name')->label('Customer'),
            TableColumn::make('status')->asBadge(OrderStatus::colors(), OrderStatus::options()),
            TableColumn::make('discount')->asMoney('USD')->align('right'),
            TableColumn::make('total')->asMoney('USD')->align('right'),
            TableColumn::make('placed_at')->label('Placed')->asDateTime('d.m.Y H:i'),
        ];
    }

    public function filters(): array
    {
        return [OptionsFilter::for('status')->options(OrderStatus::options())->multiple()];
    }

    /**
     * The action endpoint itself only needs the view permission, so every
     * action that changes data asks for update: viewers see the table but
     * not these buttons, and a hand-made request is refused with a 403.
     */
    private const UPDATE = 'admin.orders.update';

    public function actions(): array
    {
        return [
            // A button on every row, with a confirmation.
            Button::make('Advance')->method('advance')->icon('play')->position(['row'])
                ->confirm('Move the order to its next status?')->permission(self::UPDATE),

            // A form in a dialog before the method runs, on a row or a selection.
            ModalAction::make('Add a note')->withName('note')->method('addNote')->icon('message-square')
                ->position(['row', 'bulk'])->permission(self::UPDATE)
                ->modalTitle('A note for the selected orders')
                ->submitLabel('Add the note')
                ->fields([Textarea::make('note')->required()->rows(3)->rules(['max:500'])]),

            // A menu of actions.
            DropDown::make('Status')->icon('list')->position(['row'])->permission(self::UPDATE)->items([
                Button::make('Mark as paid')->withName('pay')->method('pay')->icon('credit-card'),
                Button::make('Cancel the order')->withName('cancel')->method('cancel')->icon('x-circle')
                    ->destructive()->confirm(['title' => 'Cancel the order?', 'message' => 'Only pending orders can be cancelled.', 'confirmLabel' => 'Cancel it', 'cancelLabel' => 'Keep it']),
            ]),

            // On the selection, at most 50 rows at a time.
            BulkAction::make('Mark as shipped')->withName('ship')->method('ship')->icon('truck')->requiresAtMost(50)
                ->permission(self::UPDATE),

            ModalAction::make('Apply a discount')->withName('discount')->method('applyDiscount')->icon('percent')
                ->position(['bulk'])->permission(self::UPDATE)
                ->fields([Number::make('amount')->title('Discount, $')->required()->min(0)->max(500)->step(0.01)]),

            // Above the table, with no selection at all.
            Button::make('Count overdue orders')->withName('overdue')->method('overdue')->icon('clock')
                ->position(['header'])->standalone(),
        ];
    }

    /**
     * A row action gets its own row as a one-element list. A refusal with a
     * reason is an ActionFailedException: the panel shows it, with a 422.
     *
     * @param  list<int>  $ids
     */
    public function advance(array $ids): string
    {
        $order = Order::query()->findOrFail($ids[0]);
        $next = $order->status->next()[0] ?? null;
        if ($next === null) {
            throw new ActionFailedException("{$order->number} is closed: it has no next status.");
        }
        $order->transitionTo($next);

        return "{$order->number}: ".$next->label();
    }

    /**
     * @param  list<int>  $ids
     * @param  array{note: string}  $payload  The values of the dialog's fields, already validated.
     */
    public function addNote(array $ids, array $payload): array
    {
        $count = 0;
        foreach (Order::query()->whereKey($ids)->get() as $order) {
            $order->update(['notes' => trim($order->notes."\n".$payload['note'])]);
            $count++;
        }

        return ['message' => "Note added to {$count} order(s)", 'affected' => $count];
    }

    /** @param list<int> $ids */
    public function pay(array $ids): int
    {
        $order = Order::query()->findOrFail($ids[0]);
        if ($order->status !== OrderStatus::Pending) {
            throw new ActionFailedException(__('Only a pending order can be paid.'));
        }
        $order->transitionTo(OrderStatus::Paid);

        return 1;
    }

    /** @param list<int> $ids */
    public function cancel(array $ids): int
    {
        $order = Order::query()->findOrFail($ids[0]);
        if ($order->status !== OrderStatus::Pending) {
            throw new ActionFailedException(__('Only a pending order can be cancelled.'));
        }
        $order->transitionTo(OrderStatus::Cancelled);

        return 1;
    }

    /**
     * An integer is reported as the number of affected records.
     *
     * @param  list<int>  $ids
     */
    public function ship(array $ids): int
    {
        $count = 0;
        foreach (Order::query()->whereKey($ids)->where('status', OrderStatus::Paid->value)->get() as $order) {
            $order->transitionTo(OrderStatus::Shipped);
            $count++;
        }

        return $count;
    }

    /**
     * @param  list<int>  $ids
     * @param  array{amount: float|string}  $payload
     */
    public function applyDiscount(array $ids, array $payload): int
    {
        $amount = (float) $payload['amount'];
        $count = 0;
        foreach (Order::query()->whereKey($ids)->get() as $order) {
            $order->update([
                'discount' => $amount,
                'total' => max(0, (float) $order->subtotal + (float) $order->shipping - $amount),
            ]);
            $count++;
        }

        return $count;
    }

    /**
     * A standalone action receives no ids; a string is the toast's message.
     *
     * @param  list<int>  $ids
     */
    public function overdue(array $ids): string
    {
        $count = Order::query()->where('status', OrderStatus::Pending->value)
            ->where('placed_at', '<', now()->subWeek())->count();

        return "{$count} pending order(s) are older than a week";
    }

    public function indexQuery(): Builder
    {
        return parent::indexQuery()->with('customer');
    }

    public function defaultOrder(): array
    {
        return [['column' => 'placed_at', 'direction' => 'desc']];
    }
}

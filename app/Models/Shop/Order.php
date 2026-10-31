<?php

namespace App\Models\Shop;

use App\Enums\OrderStatus;
use Dskripchenko\LaravelAdmin\Audit\Concerns\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Validation\ValidationException;

class Order extends Model
{
    use Loggable;

    protected $guarded = ['id'];

    protected $appends = ['customer_name', 'items_count'];

    protected function casts(): array
    {
        return [
            'status' => OrderStatus::class,
            'subtotal' => 'decimal:2',
            'shipping' => 'decimal:2',
            'discount' => 'decimal:2',
            'total' => 'decimal:2',
            'placed_at' => 'datetime',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'delivered_at' => 'datetime',
        ];
    }

    /**
     * An order moves only along its flow (OrderStatus::next()), whichever way
     * the status is changed — the form, a row action or a card dragged on the
     * kanban board — and each step stamps its date once.
     */
    protected static function booted(): void
    {
        static::updating(function (Order $order): void {
            if (! $order->isDirty('status')) {
                return;
            }
            $from = $order->getOriginal('status');
            $to = $order->status;
            if ($from instanceof OrderStatus && ! in_array($to, $from->next(), true)) {
                throw ValidationException::withMessages(['status' => __('An order cannot go from :from to :to.', [
                    'from' => __($from->label()),
                    'to' => __($to->label()),
                ])]);
            }
            match ($to) {
                OrderStatus::Paid => $order->paid_at ??= now(),
                OrderStatus::Shipped => $order->shipped_at ??= now(),
                OrderStatus::Delivered => $order->delivered_at ??= now(),
                default => null,
            };
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getCustomerNameAttribute(): ?string
    {
        return $this->customer?->name;
    }

    public function getItemsCountAttribute(): int
    {
        return (int) ($this->attributes['items_count'] ?? $this->items()->count());
    }

    /** Recomputes the totals from the line items. */
    public function recalculate(): void
    {
        $this->subtotal = (string) $this->items()->sum('total');
        $this->total = (string) max(0, (float) $this->subtotal + (float) $this->shipping - (float) $this->discount);
        $this->save();
    }

    /**
     * Moves the order along its status flow; the update hook checks the step
     * and stamps the matching date.
     */
    public function transitionTo(OrderStatus $status): void
    {
        $this->status = $status;
        $this->save();
    }
}

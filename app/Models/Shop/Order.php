<?php

namespace App\Models\Shop;

use App\Enums\OrderStatus;
use Dskripchenko\LaravelAdmin\Audit\Concerns\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** Moves the order along its status flow, stamping the matching date. */
    public function transitionTo(OrderStatus $status): void
    {
        $this->status = $status;
        match ($status) {
            OrderStatus::Paid => $this->paid_at ??= now(),
            OrderStatus::Shipped => $this->shipped_at ??= now(),
            OrderStatus::Delivered => $this->delivered_at ??= now(),
            default => null,
        };
        $this->save();
    }
}

<?php

namespace App\Enums;

enum OrderStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Paid = 'paid';
    case Shipped = 'shipped';
    case Delivered = 'delivered';
    case Cancelled = 'cancelled';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Paid => 'Paid',
            self::Shipped => 'Shipped',
            self::Delivered => 'Delivered',
            self::Cancelled => 'Cancelled',
            self::Refunded => 'Refunded',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Paid => 'info',
            self::Shipped => 'info',
            self::Delivered => 'success',
            self::Cancelled => 'neutral',
            self::Refunded => 'danger',
        };
    }

    /**
     * The statuses an order may move to from this one.
     *
     * @return list<self>
     */
    public function next(): array
    {
        return match ($this) {
            self::Pending => [self::Paid, self::Cancelled],
            self::Paid => [self::Shipped, self::Refunded],
            self::Shipped => [self::Delivered],
            self::Delivered => [self::Refunded],
            self::Cancelled, self::Refunded => [],
        };
    }

    /** Statuses that count as revenue. */
    public static function revenue(): array
    {
        return [self::Paid->value, self::Shipped->value, self::Delivered->value];
    }
}

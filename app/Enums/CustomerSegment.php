<?php

namespace App\Enums;

enum CustomerSegment: string
{
    use HasOptions;

    case New = 'new';
    case Regular = 'regular';
    case Vip = 'vip';

    public function label(): string
    {
        return match ($this) {
            self::New => 'New',
            self::Regular => 'Regular',
            self::Vip => 'VIP',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'info',
            self::Regular => 'neutral',
            self::Vip => 'success',
        };
    }
}

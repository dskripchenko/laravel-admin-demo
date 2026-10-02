<?php

namespace App\Enums;

enum PostStatus: string
{
    use HasOptions;

    case Draft = 'draft';
    case Review = 'review';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'In review',
            self::Published => 'Published',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'neutral',
            self::Review => 'warning',
            self::Published => 'success',
        };
    }
}

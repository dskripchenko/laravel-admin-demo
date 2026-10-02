<?php

namespace App\Enums;

/**
 * Label/colour helpers shared by the status enums: `options()` feeds a Select
 * or a filter, `colors()` feeds TableColumn::asBadge().
 */
trait HasOptions
{
    abstract public function label(): string;

    abstract public function color(): string;

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    /** @return array<string, string> */
    public static function colors(): array
    {
        $colors = [];
        foreach (self::cases() as $case) {
            $colors[$case->value] = $case->color();
        }

        return $colors;
    }
}

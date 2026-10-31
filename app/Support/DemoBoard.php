<?php

namespace App\Support;

use Dskripchenko\LaravelAdminBoard\Models\Board;

/**
 * The collaborative board of the demo: a planning session for the sale,
 * with frames, stickers, a shape and connectors, so the board is not empty
 * on the first visit. The seeder creates it; the showcase page creates it
 * again when a visitor has deleted it before the hourly reset.
 */
final class DemoBoard
{
    public const TITLE = 'Black Friday planning';

    public static function find(): Board
    {
        $board = Board::query()->where('title', self::TITLE)->whereNull('owner_type')->orderBy('id')->first();

        return $board ?? self::create();
    }

    public static function create(): Board
    {
        $board = new Board;
        $board->forceFill([
            'title' => self::TITLE,
            'board' => json_encode(self::content(), JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
        ])->save();

        return $board;
    }

    /**
     * The board document of @dskripchenko/board: a flat map of objects.
     *
     * @return array{version: int, objects: array<string, array<string, mixed>>, meta: array<string, mixed>}
     */
    public static function content(): array
    {
        $objects = [];
        $z = 0;
        $add = function (string $id, array $object) use (&$objects, &$z): void {
            $objects[$id] = ['id' => $id, ...$object, 'z' => 'a'.base_convert((string) $z++, 10, 36)];
        };

        $add('title', ['type' => 'text', 'x' => 0, 'y' => -90, 'w' => 640, 'h' => 50, 'text' => 'Black Friday: what we do this year', 'variant' => 'title']);

        $add('ideas', ['type' => 'frame', 'x' => 0, 'y' => 0, 'w' => 660, 'h' => 440, 'title' => 'Ideas']);
        $stickers = [
            ['s1', 30, 50, 'yellow', 'Free shipping over $50'],
            ['s2', 240, 50, 'orange', 'Gift wrapping at checkout'],
            ['s3', 450, 50, 'pink', 'Early access for VIP customers'],
            ['s4', 30, 250, 'green', 'Bundle: lamp + bulbs'],
            ['s5', 240, 250, 'blue', 'Countdown banner on the home page'],
        ];
        foreach ($stickers as [$id, $x, $y, $color, $text]) {
            $add($id, ['type' => 'sticky', 'x' => $x, 'y' => $y, 'w' => 180, 'h' => 160, 'color' => $color, 'text' => $text, 'fontSize' => 'auto', 'parent' => 'ideas']);
        }

        $add('decided', ['type' => 'frame', 'x' => 760, 'y' => 0, 'w' => 420, 'h' => 440, 'title' => 'Decided']);
        $add('launch', ['type' => 'shape', 'kind' => 'rounded', 'x' => 810, 'y' => 60, 'w' => 320, 'h' => 110, 'fill' => '#dbeafe', 'stroke' => '#3b82f6', 'text' => 'Sale starts Friday 9:00', 'textAlign' => 'center', 'parent' => 'decided']);
        $add('vip', ['type' => 'shape', 'kind' => 'rounded', 'x' => 810, 'y' => 260, 'w' => 320, 'h' => 110, 'fill' => '#dcfce7', 'stroke' => '#22c55e', 'text' => 'VIP newsletter on Thursday', 'textAlign' => 'center', 'parent' => 'decided']);
        $add('star', ['type' => 'emoji', 'x' => 1100, 'y' => 30, 'w' => 48, 'h' => 48, 'char' => '⭐', 'parent' => 'decided']);

        $add('c1', ['type' => 'connector', 'x' => 0, 'y' => 0, 'w' => 0, 'h' => 0, 'from' => ['id' => 's5', 'side' => 'right'], 'to' => ['id' => 'launch', 'side' => 'left'], 'route' => 'curve', 'endArrow' => 'arrow', 'label' => 'agreed']);
        $add('c2', ['type' => 'connector', 'x' => 0, 'y' => 0, 'w' => 0, 'h' => 0, 'from' => ['id' => 's3', 'side' => 'bottom'], 'to' => ['id' => 'vip', 'side' => 'left'], 'route' => 'elbow', 'endArrow' => 'arrow', 'strokeStyle' => 'dashed']);

        return ['version' => 1, 'objects' => $objects, 'meta' => ['title' => self::TITLE, 'background' => 'dots']];
    }
}

<?php

namespace App\Admin\Showcase\Grids;

use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Grids › Row and bulk actions.
 */
final class GridActionsScreen extends GridShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-grids-actions';
    }

    public static function icon(): string
    {
        return 'zap';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    protected static function resource(): string
    {
        return GridActionsResource::class;
    }

    public function name(): string
    {
        return 'Row actions';
    }

    public function description(): ?string
    {
        return 'Row buttons, dialogs with a form, menus, bulk actions and a header action.';
    }

    protected function demo(): array
    {
        return [
            Layout::block('What to try', [
                Layout::markdown(__("- **Advance** on a row asks for confirmation and moves the order on; on a delivered or cancelled order it is refused with a reason (`ActionFailedException`).\n- **Add a note** opens a dialog with a form; leave it empty to see the validation.\n- **Status** is a menu of two actions; **Cancel the order** has a custom confirmation.\n- Select rows: **Mark as shipped** (at most 50) and **Apply a discount** appear above the table.\n- **Count overdue orders** sits in the toolbar and runs without a selection.\n\nViewers see the table but not the actions: every action that changes data asks for `admin.orders.update` through `permission()`, and the server refuses it to anyone without.")),
            ]),
        ];
    }
}

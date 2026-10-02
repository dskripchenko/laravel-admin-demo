<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Infolist\IconEntry;
use Dskripchenko\LaravelAdmin\Infolist\TextEntry;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Permission\PermissionCheck;

/**
 * Actions › Permissions: Action::permission() and canSee() are enforced on the
 * server. A button the user may not press is left out of the command bar, and
 * calling its method anyway is refused with 403 (`action_forbidden`).
 * Sign in as the Viewer or the Editor to see the bar shrink.
 */
final class PermissionsScreen extends ShowcaseScreen
{
    /** The method of each button => [label, the permission it requires]. */
    private const GATES = [
        'pressAnyone' => ['Anyone', null],
        'viewProducts' => ['Viewers', 'admin.products.view'],
        'publishProducts' => ['Editors', 'admin.products.update'],
        'shipOrders' => ['Orders', 'admin.orders.update'],
        'editRoles' => ['Roles', 'admin.system-roles.update'],
    ];

    public static function slug(): string
    {
        return 'showcase-actions-permissions';
    }

    public static function group(): string
    {
        return 'actions';
    }

    public static function icon(): string
    {
        return 'lock';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Permissions';
    }

    public function description(): ?string
    {
        return 'Gated buttons: hidden from the bar, refused by the server.';
    }

    /** Whether the signed-in user may press each button. */
    public function query(mixed ...$params): array
    {
        $state = ['user' => auth('admin')->user()?->getAttribute('name')];
        foreach (self::GATES as $method => [, $permission]) {
            $state['gate_'.$method] = PermissionCheck::allows($permission);
        }
        $state['gate_weekday'] = now()->isWeekday();

        return $state;
    }

    protected function demo(): array
    {
        $entries = [TextEntry::make('user')->label('Signed in as')];
        foreach (self::GATES as $method => [$label, $permission]) {
            $entries[] = IconEntry::make('gate_'.$method)
                ->label(__($label).' — '.($permission ?? __('no permission')))
                ->trueLabel('Shown')->falseLabel('Hidden')
                ->trueIcon('check-circle')->falseIcon('x-circle');
        }
        $entries[] = IconEntry::make('gate_weekday')->label(__('Weekdays').' — canSee()')
            ->trueLabel('Shown')->falseLabel('Hidden')
            ->trueIcon('check-circle')->falseIcon('x-circle');

        return [
            Layout::columns([
                Layout::block('What you can press', [Layout::infolist($entries)])
                    ->description(__('Computed for the signed-in user; the command bar above shows the same.')),
                Layout::markdown(implode("\n\n", [
                    __('The **Administrator** sees every button, the **Editor** the product ones, the **Viewer** only Anyone and Viewers. Weekdays follows the calendar, not a permission.'),
                    __('The check happens on the server: an action the user may not run never reaches the manifest, and posting its method to `runMethod` anyway answers `403` with `errorKey: action_forbidden`. A method no action names is guarded by the screen\'s own `permission()` only.'),
                    '> **Tip** '.__('`canSee()` takes a closure without arguments, evaluated when the screen is serialized — here, a button shown on weekdays only.'),
                ]))->card(),
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Anyone')->method('pressAnyone')->icon('users'),
            Button::make('Viewers')->method('viewProducts')->permission('admin.products.view')->icon('eye'),
            Button::make('Editors')->method('publishProducts')->permission('admin.products.update')->icon('pencil'),
            Button::make('Orders')->method('shipOrders')->permission('admin.orders.update')->icon('truck'),
            Button::make('Roles')->method('editRoles')->permission('admin.system-roles.update')->icon('shield'),
            Button::make('Weekdays')->method('weekdayReport')->canSee(fn () => now()->isWeekday())->icon('calendar'),
        ];
    }

    public function pressAnyone(): array
    {
        return ['message' => __('Allowed for everyone signed in.')];
    }

    public function viewProducts(): array
    {
        return ['message' => __('Allowed: you hold :permission.', ['permission' => 'admin.products.view'])];
    }

    public function publishProducts(): array
    {
        return ['message' => __('Allowed: you hold :permission.', ['permission' => 'admin.products.update'])];
    }

    public function shipOrders(): array
    {
        return ['message' => __('Allowed: you hold :permission.', ['permission' => 'admin.orders.update'])];
    }

    public function editRoles(): array
    {
        return ['message' => __('Allowed: you hold :permission.', ['permission' => 'admin.system-roles.update'])];
    }

    public function weekdayReport(): array
    {
        return ['message' => __('Shown and allowed on weekdays.')];
    }
}

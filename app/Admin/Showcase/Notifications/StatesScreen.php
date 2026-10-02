<?php

namespace App\Admin\Showcase\Notifications;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Action\Link;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\RelationTable;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Dskripchenko\LaravelAdmin\Table\TableColumn;
use Illuminate\Validation\ValidationException;

/**
 * Notifications › Empty and error states: field errors from the server, a
 * refusal with a reason, an action only some users may run, an empty table,
 * and the demo stand's read-only refusal.
 */
final class StatesScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-notifications-states';
    }

    public static function group(): string
    {
        return 'notifications';
    }

    public static function icon(): string
    {
        return 'life-buoy';
    }

    public function name(): string
    {
        return 'Error states';
    }

    public function description(): ?string
    {
        return 'Validation errors, refusals, forbidden actions and empty tables.';
    }

    public function query(mixed ...$params): array
    {
        return ['coupon' => 'SPRING', 'quantity' => 0, 'returns' => []];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Errors under the fields', [
                    Layout::rows([
                        Input::make('coupon')->help('Any code but WELCOME10 is "expired".'),
                        Number::make('quantity')->integer()->help('Must be between 1 and 10.'),
                    ]),
                ])->icon('alert-circle')->description('"Apply coupon" throws a ValidationException: the messages land under their fields.'),
                Layout::block('An empty table', [
                    RelationTable::make('returns')->title('Returns this week')->columns([
                        TableColumn::make('number')->label('Order'),
                        TableColumn::make('reason'),
                        TableColumn::make('amount')->asMoney('USD'),
                    ]),
                ])->icon('inbox')->description('No rows: the table says so instead of drawing nothing.'),
            ]),
            Layout::markdown(__(<<<'MD'
**The other buttons above**

- **Refuse with a reason** — the method looks at the state and declines: a `ValidationException` naming no field of the form shows its message as an error toast.
- **Administrators only** carries `->permission('admin.system-users.update')`. The Editor and the Viewer do not see it at all, and calling its method anyway answers `403` with `errorKey: action_forbidden`.
- **Try a blocked action** opens your profile. On this demo stand changing the password is refused by the read-only guard: `403 demo_readonly`, shown as the toast "Demo mode: this action is disabled".
MD))->card(),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Apply coupon')->method('applyCoupon')->primary()->icon('check'),
            Button::make('Refuse with a reason')->method('refuse')->icon('x-circle'),
            Button::make('Administrators only')->method('adminOnly')->icon('shield')
                ->permission('admin.system-users.update'),
            Link::make('Try a blocked action')->href('/'.trim((string) config('admin.path', 'admin'), '/').'/profile')->icon('lock'),
        ];
    }

    /** @param array<string, mixed> $state */
    public function applyCoupon(array $state): array
    {
        validator($state, [
            'coupon' => ['required', 'string', 'in:WELCOME10'],
            'quantity' => ['required', 'integer', 'between:1,10'],
        ], [
            'coupon.in' => __('This coupon has expired.'),
        ])->validate();

        return ['message' => __('Coupon applied.')];
    }

    public function refuse(): array
    {
        throw ValidationException::withMessages([
            'form' => __('The order is already shipped and cannot be changed.'),
        ]);
    }

    public function adminOnly(): array
    {
        return ['message' => __('You hold admin.system-users.update, so this ran.')];
    }
}

<?php

namespace App\Admin\Showcase\Actions;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\ModalAction;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Field\TimePicker;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Actions › Modal forms: a ModalAction asks for a few values first, then
 * calls the method with them. A validation error keeps the dialog open with
 * the messages under the fields.
 */
final class ModalFormsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-actions-modal-forms';
    }

    public static function group(): string
    {
        return 'actions';
    }

    public static function icon(): string
    {
        return 'message-square';
    }

    public function name(): string
    {
        return 'Modal forms';
    }

    public function description(): ?string
    {
        return 'ModalAction: a small form in a dialog, validated on the server.';
    }

    protected function demo(): array
    {
        return [
            Layout::markdown(implode("\n\n", [
                __('**Invite a teammate** opens a dialog with three fields. Leave the email empty or type a wrong one and press Send invite: the server answers 422 and the dialog stays open with the errors.'),
                __('**Reschedule** is the same action in a larger dialog, with date and time pickers.'),
                __('The values of the dialog join the screen\'s state, so the method receives one payload.'),
            ]))->card(),
        ];
    }

    public function commandBar(): array
    {
        return [
            ModalAction::make('Invite a teammate')->method('invite')->icon('user-plus')->primary()
                ->modalTitle('Invite a teammate')
                ->submitLabel('Send invite')
                ->modalSize('md')
                ->fields([
                    Input::make('invite_email')->title('Email')->type('email')->required(),
                    Select::make('invite_role')->title('Role')->options([
                        'editor' => 'Editor',
                        'viewer' => 'Viewer',
                        'support' => 'Support',
                    ])->default('viewer')->required(),
                    Textarea::make('invite_note')->title('Personal note')->rows(3)
                        ->placeholder('Optional — goes into the email'),
                ]),
            ModalAction::make('Reschedule')->method('reschedule')->icon('calendar')
                ->modalTitle('Move the delivery')
                ->submitLabel('Reschedule')
                ->modalSize('lg')
                ->fields([
                    DatePicker::make('delivery_date')->title('New date')->min(now()->toDateString())->required(),
                    TimePicker::make('delivery_time')->title('Time')->step(30),
                    Textarea::make('delivery_reason')->title('Reason')->rows(2)->required(),
                ]),
        ];
    }

    /** @param array<string, mixed> $state */
    public function invite(array $state): array
    {
        $data = validator($state, [
            'invite_email' => 'required|email',
            'invite_role' => 'required|in:editor,viewer,support',
            'invite_note' => 'nullable|max:500',
        ], attributes: ['invite_email' => __('email'), 'invite_role' => __('role'), 'invite_note' => __('note')])->validate();

        return ['message' => __('An invitation to :email as :role would be sent now.', [
            'email' => $data['invite_email'],
            'role' => $data['invite_role'],
        ])];
    }

    /** @param array<string, mixed> $state */
    public function reschedule(array $state): array
    {
        $data = validator($state, [
            'delivery_date' => 'required|date|after_or_equal:today',
            'delivery_reason' => 'required|min:5',
        ], attributes: ['delivery_date' => __('date'), 'delivery_reason' => __('reason')])->validate();

        return ['message' => __('The delivery would move to :date.', [
            'date' => trim($data['delivery_date'].' '.($state['delivery_time'] ?? '')),
        ])];
    }
}

<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Radio;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\TagsInput;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Basics: the everyday fields, laid out on the 12-column grid, with
 * server-side validation on submit. Nothing is saved.
 */
final class FormBasicsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-basics';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public function name(): string
    {
        return 'Form basics';
    }

    public function description(): ?string
    {
        return 'Inputs, selects, dates and switches; validated on the server.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'plan' => 'team',
            'seats' => 5,
            'starts_on' => now()->addWeek()->toDateString(),
            'billing' => 'yearly',
            'newsletter' => true,
            'tags' => ['laravel', 'admin'],
            'notes' => '',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('Sign-up', [
                Layout::rows([
                    Input::make('name')->required()->span(6),
                    Input::make('email')->type('email')->required()->span(6),
                    Select::make('plan')->options([
                        'starter' => 'Starter',
                        'team' => 'Team',
                        'enterprise' => 'Enterprise',
                    ])->required()->span(4),
                    Number::make('seats')->integer()->min(1)->max(500)->span(4),
                    DatePicker::make('starts_on')->title('Start date')->span(4),
                    Radio::make('billing')->options(['monthly' => 'Monthly', 'yearly' => 'Yearly'])->inline(),
                    TagsInput::make('tags')->suggestions(['laravel', 'admin', 'vue', 'php']),
                    Textarea::make('notes')->rows(3)->placeholder('Anything we should know?'),
                    Switcher::make('newsletter')->title('Send me product news'),
                ]),
            ])->description('Change the values and press Submit — try an empty name or a wrong email.'),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make('Submit')->method('submit')->primary()->icon('send')];
    }

    /** @param array<string, mixed> $state */
    public function submit(array $state): array
    {
        $data = validator($state, [
            'name' => 'required|min:2',
            'email' => 'required|email',
            'plan' => 'required|in:starter,team,enterprise',
            'seats' => 'nullable|integer|min:1|max:500',
        ])->validate();

        return ['message' => __('Valid! :name would get the :plan plan.', ['name' => $data['name'], 'plan' => $data['plan']])];
    }
}

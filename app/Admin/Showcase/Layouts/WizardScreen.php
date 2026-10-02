<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Label;
use Dskripchenko\LaravelAdmin\Field\Password;
use Dskripchenko\LaravelAdmin\Field\Radio;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Switcher;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Wizard: a long form in steps. The linear wizard checks each step
 * before letting you on; the free-form one lets you jump around. Both remember
 * their progress in the browser and submit to a method of the screen.
 */
final class WizardScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-wizard';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'workflow';
    }

    public function name(): string
    {
        return 'Wizard';
    }

    public function description(): ?string
    {
        return 'Layout::wizard() — steps validated one by one, progress kept across reloads.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'email' => '',
            'password' => '',
            'company' => '',
            'team_size' => '2-10',
            'plan' => 'team',
            'starts_on' => now()->addDay()->toDateString(),
            'survey_source' => null,
            'survey_role' => '',
            'survey_feedback' => '',
            'survey_contact' => false,
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::tabs([
                'Linear' => [
                    Layout::wizard([
                        Layout::step('Account', [
                            Input::make('email')->type('email')->required(),
                            Password::make('password')->required()->revealable()->help('At least 8 characters'),
                        ])->description(__('Sign-in details'))->icon('user')
                            ->rules(['password' => ['required', 'min:8']]),
                        Layout::step('Company', [
                            Input::make('company')->required(),
                            Select::make('team_size')->title('Team size')->options([
                                '1' => 'Just me', '2-10' => '2–10', '11-50' => '11–50', '50+' => 'More than 50',
                            ]),
                        ])->description(__('Who you are'))->icon('building'),
                        Layout::step('Plan', [
                            Radio::make('plan')->options(['starter' => 'Starter', 'team' => 'Team', 'enterprise' => 'Enterprise'])->inline(),
                            DatePicker::make('starts_on')->title('Start date')->required(),
                        ])->description(__('What you pay for'))->icon('credit-card'),
                        Layout::step('Review', [
                            Label::make('email'),
                            Label::make('company'),
                            Label::make('plan'),
                            Label::make('starts_on')->title('Start date'),
                        ])->description(__('Check and finish'))->icon('check-circle'),
                    ])->submit('finishSignup')->persistKey('showcase-signup'),
                ],
                'Free form' => [
                    Layout::wizard([
                        Layout::step('Source', [
                            Select::make('survey_source')->title('How did you find us?')->options([
                                'search' => 'Search engine', 'friend' => 'A friend', 'github' => 'GitHub', 'other' => 'Other',
                            ]),
                        ]),
                        Layout::step('About you', [
                            Input::make('survey_role')->title('Your role'),
                        ]),
                        Layout::step('Feedback', [
                            Textarea::make('survey_feedback')->title('What should we improve?')->rows(4),
                            Switcher::make('survey_contact')->title('You may contact me'),
                        ]),
                    ])->freeForm()->submit('sendSurvey')->persistKey('showcase-survey'),
                ],
            ]),
        ];
    }

    /** @param array<string, mixed> $state */
    public function finishSignup(array $state): array
    {
        $data = validator($state, [
            'email' => 'required|email',
            'password' => 'required|min:8',
            'company' => 'required',
            'plan' => 'required|in:starter,team,enterprise',
            'starts_on' => 'required|date',
        ])->validate();

        return ['message' => __('Welcome aboard, :company! (Nothing was created: this is the showcase.)', ['company' => $data['company']])];
    }

    /** @param array<string, mixed> $state */
    public function sendSurvey(array $state): array
    {
        return ['message' => __('Thanks for the feedback!')];
    }
}

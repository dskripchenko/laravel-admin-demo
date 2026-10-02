<?php

namespace App\Admin\Showcase\Layouts;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Checkbox;
use Dskripchenko\LaravelAdmin\Field\DateRange;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Label;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Layouts › Modals and drawers: parts of the screen's layout shown in an
 * overlay. A command-bar button opens one by its id; the fields inside edit
 * the same state as the rest of the screen.
 */
final class ModalsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-layouts-modals';
    }

    public static function group(): string
    {
        return 'layouts';
    }

    public static function icon(): string
    {
        return 'layers';
    }

    public function name(): string
    {
        return 'Modals and drawers';
    }

    public function description(): ?string
    {
        return 'Layout::modal() and Layout::drawer(), opened by Button::opens().';
    }

    public function query(mixed ...$params): array
    {
        return [
            'name' => 'Ada Lovelace',
            'email' => 'ada@example.com',
            'bio' => 'Mathematician; wrote the first program.',
            'status' => 'paid',
            'period' => ['from' => now()->subDays(30)->toDateString(), 'to' => now()->toDateString()],
            'terms' => false,
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('Profile', [
                Label::make('name'),
                Label::make('email'),
                Label::make('status')->title('Status filter'),
            ])->description(__('These values live in the screen\'s state; the overlays edit the same keys.')),

            Layout::modal('Edit the profile', [
                Input::make('name')->required(),
                Input::make('email')->type('email')->required(),
                Textarea::make('bio')->rows(3),
            ])
                ->withId('profile-modal')
                ->size('lg')
                ->footer([
                    Button::make('Cancel')->withName('cancel'),
                    Button::make('Save')->method('saveProfile')->primary(),
                ]),

            Layout::modal('Terms of service', [
                Layout::markdown(__('This dialog has no cross and ignores Escape and clicks outside: `dismissable(false)`. Only the footer closes it.')),
                Checkbox::make('terms')->title('I have read the terms'),
            ])
                ->withId('terms-modal')
                ->size('sm')
                ->dismissable(false)
                ->footer([
                    Button::make('Decline')->withName('close'),
                    Button::make('Accept')->method('acceptTerms')->primary(),
                ]),

            Layout::drawer('Filters', [
                Layout::markdown(__('The fields edit the screen\'s state as you change them; Apply filters in the command bar sends it to the server.')),
                Select::make('status')->options(['pending' => 'Pending', 'paid' => 'Paid', 'shipped' => 'Shipped']),
                DateRange::make('period'),
            ])
                ->withId('filters-drawer')
                ->position('right')
                ->size('md'),

            Layout::drawer('Activity log', [
                Layout::markdown(implode("\n", [
                    '- 09:12 — '.__('Order ORD-10421 paid'),
                    '- 09:30 — '.__('Product “Linen shirt” restocked'),
                    '- 10:05 — '.__('A new customer signed up'),
                ])),
            ])
                ->withId('log-drawer')
                ->position('bottom')
                ->size('40vh'),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Edit profile')->opens('profile-modal')->icon('pencil')->primary(),
            Button::make('Terms')->opens('terms-modal')->icon('file-text'),
            Button::make('Filters')->opens('filters-drawer')->icon('filter'),
            Button::make('Activity')->opens('log-drawer')->icon('activity'),
            Button::make('Apply filters')->method('applyFilters')->icon('check'),
        ];
    }

    /** @param array<string, mixed> $state */
    public function saveProfile(array $state): array
    {
        validator($state, ['name' => 'required|min:2', 'email' => 'required|email'])->validate();

        return ['message' => __('Profile saved (in this page only).')];
    }

    /** @param array<string, mixed> $state */
    public function acceptTerms(array $state): array
    {
        validator($state, ['terms' => 'accepted'], ['terms.accepted' => __('Tick the box first.')])->validate();

        return ['message' => __('Thank you.')];
    }

    /** @param array<string, mixed> $state */
    public function applyFilters(array $state): array
    {
        return ['message' => __('Filters applied: :status.', ['status' => $state['status'] ?? '—'])];
    }
}

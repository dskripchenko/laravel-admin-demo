<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\DateRange;
use Dskripchenko\LaravelAdmin\Field\TimePicker;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Dates and times: a date with limits, a date with a time, a range
 * with shortcuts and a time with a step.
 */
final class DatesScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-dates';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'calendar';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Dates and times';
    }

    public function description(): ?string
    {
        return 'DatePicker with limits and a time, DateRange with presets, TimePicker.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'starts_on' => now()->addDays(3)->toDateString(),
            'birthday' => '1990-06-15',
            'publish_at' => now()->addDay()->setTime(9, 30)->format('Y-m-d H:i:s'),
            'period' => ['from' => now()->subDays(29)->toDateString(), 'to' => now()->toDateString()],
            'opens_at' => '09:00',
            'closes_at' => '18:30:00',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('Dates', [
                Layout::rows([
                    DatePicker::make('starts_on')->title('Start date')->min(now()->startOfDay())->max(now()->addYear())
                        ->help('Only the next twelve months can be picked')->span(4),
                    DatePicker::make('birthday')->min('1900-01-01')->max(now())->span(4),
                    DatePicker::make('publish_at')->title('Publish at')->withTime()->span(4)
                        ->help('Stored as Y-m-d H:i:s'),
                    DateRange::make('period')->title('Report period')
                        ->presets(['today', 'last_7_days', 'last_30_days', 'this_month', 'last_month', 'this_year']),
                ]),
            ])->icon('calendar'),
            Layout::block('Times', [
                Layout::rows([
                    TimePicker::make('opens_at')->title('Opens at')->step(15)->span(6)->help('A 15-minute step'),
                    TimePicker::make('closes_at')->title('Closes at')->withSeconds()->span(6),
                ]),
            ])->icon('clock'),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make('Submit')->method('submit')->primary()->icon('send')];
    }

    /** @param array<string, mixed> $state */
    public function submit(array $state): array
    {
        validator($state, [
            'starts_on' => 'required|date|after_or_equal:today',
            'birthday' => 'nullable|date|before:today',
            'publish_at' => 'nullable|date',
            'period.from' => 'required|date',
            'period.to' => 'required|date|after_or_equal:period.from',
            'opens_at' => 'nullable|date_format:H:i',
        ])->validate();

        return ['message' => __('Valid! The period has :days days.', [
            'days' => (int) round(abs(strtotime($state['period']['to']) - strtotime($state['period']['from'])) / 86400) + 1,
        ])];
    }
}

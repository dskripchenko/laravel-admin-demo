<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\DatePicker;
use Dskripchenko\LaravelAdmin\Field\Field;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Field\Password;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\ValidationRulesExporter;
use Dskripchenko\LaravelAdmin\Layout\Layout;
use Illuminate\Validation\ValidationException;

/**
 * Forms › Validation and errors: the limits are declared once, on the
 * fields. ValidationRulesExporter turns them into Laravel rules — the
 * explicit ones from rules() and required(), plus the implicit ones each
 * type adds (email, numeric, integer, min/max, date). A check only the server
 * can make throws ValidationException; either way the messages appear under
 * the fields.
 */
final class ValidationScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-validation';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'shield-check';
    }

    public function name(): string
    {
        return 'Validation and errors';
    }

    public function description(): ?string
    {
        return 'Rules declared on the fields, checked on the server, shown under the fields.';
    }

    public function query(mixed ...$params): array
    {
        return [
            'username' => 'admin',
            'email' => 'not-an-email',
            'age' => 12,
            'team_size' => 2.5,
            'country' => '',
            'website' => 'example',
            'joined_on' => '2021-04-12',
            'password' => 'short',
            'password_confirmation' => 'different',
        ];
    }

    /**
     * The fields: the form and the rules in one place.
     *
     * @return list<Field>
     */
    private function fields(): array
    {
        return [
            Input::make('username')->required()->rules(['alpha_dash', 'min:3', 'max:20'])->span(6)
                ->help('“admin” is taken — only the server knows that'),
            Input::make('email')->type('email')->required()->span(6),
            Number::make('age')->integer()->min(18)->max(120)->span(4),
            Number::make('team_size')->title('Team size')->integer()->min(1)->span(4),
            Select::make('country')->options(['gb' => 'United Kingdom', 'de' => 'Germany', 'fr' => 'France'])
                ->required()->rules(['in:gb,de,fr'])->span(4),
            Input::make('website')->type('url')->rules(['url'])->span(6),
            DatePicker::make('joined_on')->title('Joined on')->span(6),
            Password::make('password')->required()->rules(['min:8'])->confirmed()->span(6),
            Password::make('password_confirmation')->title('Repeat the password')->span(6),
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::block('Sign-up with mistakes', [Layout::rows($this->fields())])
                ->icon('shield-check')
                ->description('Every value is wrong on purpose: press Check and read the messages, then fix them one by one.'),
            Layout::markdown(__('The rules the server derived from these fields:')."\n\n```php\n"
                .$this->exportedRules()."\n```"),
        ];
    }

    public function commandBar(): array
    {
        return [Button::make('Check')->method('check')->primary()->icon('check')];
    }

    /** @param array<string, mixed> $state */
    public function check(array $state): array
    {
        $data = validator($state, ValidationRulesExporter::export($this->fields()))->validate();

        // A rule no field can express: the database knows which names are taken.
        if (in_array(strtolower((string) $data['username']), ['admin', 'root', 'support'], true)) {
            throw ValidationException::withMessages(['username' => __('This username is already taken.')]);
        }

        return ['message' => __('All good, :name!', ['name' => $data['username']])];
    }

    private function exportedRules(): string
    {
        $lines = [];
        foreach (ValidationRulesExporter::export($this->fields()) as $field => $rules) {
            $rules = array_map(fn (mixed $rule) => is_string($rule) ? $rule : class_basename($rule), $rules);
            $lines[] = "'{$field}' => ['".implode("', '", $rules)."'],";
        }

        return implode("\n", $lines);
    }
}

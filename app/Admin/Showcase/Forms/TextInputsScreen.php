<?php

namespace App\Admin\Showcase\Forms;

use App\Admin\Showcase\ShowcaseScreen;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Generated;
use Dskripchenko\LaravelAdmin\Field\Hidden;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Label;
use Dskripchenko\LaravelAdmin\Field\Password;
use Dskripchenko\LaravelAdmin\Field\Slug;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Layout;

/**
 * Forms › Text inputs: everything typed by hand — the Input types, a
 * textarea, a password with its confirmation, a generated key, a slug that
 * follows its title, and the fields that are never typed into at all.
 */
final class TextInputsScreen extends ShowcaseScreen
{
    public static function slug(): string
    {
        return 'showcase-forms-text';
    }

    public static function group(): string
    {
        return 'forms';
    }

    public static function icon(): string
    {
        return 'pencil';
    }

    public static function badge(): ?string
    {
        return 'new';
    }

    public function name(): string
    {
        return 'Text inputs';
    }

    public function description(): ?string
    {
        return 'Input types, Textarea, Password, Generated, Slug, Hidden and Label.';
    }

    public function query(mixed ...$params): array
    {
        $title = 'Щука и ёж: a winter story';

        return [
            'id' => 1042,
            'uuid' => '3f6c0a52-8f1e-4c9b-9d55-6e1d2b7a0c11',
            'title' => $title,
            // The server's conversion is the one the browser runs as you type.
            'slug' => Slug::generate($title),
            'email' => 'ada@example.com',
            'phone' => '+44 20 7946 0000',
            'website' => 'https://example.com',
            'summary' => "Two lines of text.\nA textarea keeps the line breaks.",
            'password' => '',
            'password_confirmation' => '',
            'api_key' => '',
        ];
    }

    protected function demo(): array
    {
        return [
            Layout::columns([
                Layout::block('Typed by hand', [
                    Layout::rows([
                        Input::make('title')->required()->placeholder('Type a title — the slug follows'),
                        Slug::make('slug')->from('title')->help('Filled from the title until you edit it; clear it to hand it back'),
                        Input::make('email')->type('email')->span(6),
                        Input::make('phone')->type('tel')->span(6),
                        Input::make('website')->type('url')->placeholder('https://'),
                        Textarea::make('summary')->rows(3),
                    ]),
                ])->icon('pencil'),
                Layout::block('Secrets and service fields', [
                    Layout::rows([
                        Password::make('password')->revealable()->confirmed()->span(6),
                        Password::make('password_confirmation')->title('Repeat the password')->revealable()->span(6),
                        Generated::make('api_key')->title('API key')->length(32)->charset('abcdef0123456789')
                            ->help('Generated in the browser; press Generate for another one'),
                        Hidden::make('uuid'),
                        Label::make('id')->title('Record ID'),
                        Label::make('note')->title('Note')->value('Labels show a value and are never submitted.'),
                    ]),
                ])->icon('lock'),
            ]),
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
            'title' => 'required|max:120',
            'slug' => 'nullable|alpha_dash',
            'email' => 'nullable|email',
            'website' => 'nullable|url',
            'password' => 'nullable|min:8|confirmed',
        ])->validate();

        return ['message' => __('Valid! The slug is “:slug”.', ['slug' => $state['slug'] ?? ''])];
    }
}

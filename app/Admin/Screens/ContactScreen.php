<?php

declare(strict_types=1);

namespace App\Admin\Screens;

use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Select;
use Dskripchenko\LaravelAdmin\Field\Textarea;
use Dskripchenko\LaravelAdmin\Layout\Rows;
use Dskripchenko\LaravelAdmin\Screen\Screen;
use Dskripchenko\LaravelAdmin\Support\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * ContactScreen — пример кастомной формы вне CRUD.
 *
 * Демонстрирует:
 *   - Screen с произвольным state (не привязан к Eloquent-модели)
 *   - Layout::rows() с Input/Select/Textarea
 *   - commandBar с Button::method('send') — submit-кнопка
 *   - command-метод send($state) с валидацией и реальным side-effect'ом
 *     (увеличение счётчика в кэше + запись в log)
 *   - возврат `state` для очистки полей после успеха + `message` + `alerts`
 */
final class ContactScreen extends Screen
{
    public function name(): string
    {
        return 'Связаться с командой';
    }

    public function description(): ?string
    {
        return 'Отправьте сообщение через эту форму — оно попадёт нам в логи и инкрементит счётчик отправок.';
    }

    public function permission(): array|string|null
    {
        // null = только аутентификация (любой залогиненный admin может писать).
        return null;
    }

    public function query(mixed ...$params): Repository|array
    {
        return [
            'name' => '',
            'email' => '',
            'subject' => 'general',
            'message' => '',
            'sent_total' => (int) Cache::get('admin.contact.sent_total', 0),
        ];
    }

    public function layout(): array
    {
        return [
            Rows::make([
                Input::make('name')
                    ->required()
                    ->title('Имя'),
                Input::make('email')
                    ->type('email')
                    ->required()
                    ->title('E-mail для ответа'),
                Select::make('subject')
                    ->title('Тема')
                    ->options([
                        'general' => 'Общий вопрос',
                        'bug' => 'Сообщить о баге',
                        'feature' => 'Запрос функционала',
                        'other' => 'Другое',
                    ])
                    ->required(),
                Textarea::make('message')
                    ->title('Сообщение')
                    ->placeholder('Опишите проблему или предложение...')
                    ->rows(6)
                    ->required(),
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Отправить')->method('send')->primary(),
        ];
    }

    /**
     * Command-метод. SPA отправляет POST /api/admin/contact/runMethod
     * c body `{method: 'send', payload: {...state}}`.
     *
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>
     */
    public function send(array $state): array
    {
        $data = validator($state, [
            'name' => 'required|string|min:2|max:80',
            'email' => 'required|email',
            'subject' => 'required|in:general,bug,feature,other',
            'message' => 'required|string|min:10|max:4000',
        ])->validate();

        Log::info('admin.contact.message', [
            'from' => $data['email'],
            'name' => $data['name'],
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        $total = (int) Cache::get('admin.contact.sent_total', 0) + 1;
        Cache::put('admin.contact.sent_total', $total);

        return [
            'message' => 'Сообщение отправлено, спасибо!',
            'alerts' => [
                ['type' => 'success', 'message' => 'Получили #'.$total.' — ответим в течение суток.'],
            ],
            'state' => [
                'name' => '',
                'email' => '',
                'subject' => 'general',
                'message' => '',
                'sent_total' => $total,
            ],
        ];
    }
}

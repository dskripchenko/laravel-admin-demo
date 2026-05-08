<?php

declare(strict_types=1);

namespace App\Admin\Screens;

use App\Models\Article;
use Dskripchenko\LaravelAdmin\Action\Button;
use Dskripchenko\LaravelAdmin\Field\Input;
use Dskripchenko\LaravelAdmin\Field\Number;
use Dskripchenko\LaravelAdmin\Layout\Block;
use Dskripchenko\LaravelAdmin\Layout\Columns;
use Dskripchenko\LaravelAdmin\Layout\Rows;
use Dskripchenko\LaravelAdmin\Screen\Screen;
use Dskripchenko\LaravelAdmin\Support\Repository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * SystemStatusScreen — пример read-only кастомной страницы (без формы).
 *
 * Показывает статус demo-приложения: количество статей по статусам,
 * количество сообщений из ContactScreen, статус DB-соединения.
 *
 * Демонстрирует:
 *   - Screen без формы — собирает данные в query() и отрисовывает через
 *     Block + readonly Input/Number (read-only представление полей)
 *   - commandBar с двумя командами: refresh (обновить snapshot)
 *     + resetContactCounter (сбросить счётчик контактов с confirm-диалогом)
 *   - refresh:true в ответе runMethod заставляет фронт перезагрузить state
 */
final class SystemStatusScreen extends Screen
{
    public function name(): string
    {
        return 'Статус системы';
    }

    public function description(): ?string
    {
        return 'Сводка по demo-приложению: статьи, сообщения, БД и окружение.';
    }

    public function permission(): array|string|null
    {
        return null;
    }

    public function query(mixed ...$params): Repository|array
    {
        $articleStats = [
            'total' => (int) Article::query()->count(),
            'published' => (int) Article::query()->where('status', 'published')->count(),
            'draft' => (int) Article::query()->where('status', 'draft')->count(),
            'review' => (int) Article::query()->where('status', 'review')->count(),
            'archived' => (int) Article::query()->where('status', 'archived')->count(),
        ];

        try {
            DB::connection()->getPdo();
            $dbStatus = 'connected';
            $dbDriver = (string) DB::connection()->getDriverName();
        } catch (\Throwable) {
            $dbStatus = 'error';
            $dbDriver = 'unknown';
        }

        return [
            'articles_total' => $articleStats['total'],
            'articles_published' => $articleStats['published'],
            'articles_draft' => $articleStats['draft'],
            'articles_review' => $articleStats['review'],
            'articles_archived' => $articleStats['archived'],
            'contact_messages' => (int) Cache::get('admin.contact.sent_total', 0),
            'db_status' => $dbStatus,
            'db_driver' => $dbDriver,
            'php_version' => PHP_VERSION,
            'app_env' => (string) config('app.env', 'unknown'),
            'snapshot_at' => now()->format('Y-m-d H:i:s'),
        ];
    }

    public function layout(): array
    {
        return [
            Rows::make([
                Block::make('Контент', [
                    Columns::make([
                        Number::make('articles_total')->title('Всего статей')->readonly(),
                        Number::make('articles_published')->title('Опубликовано')->readonly(),
                        Number::make('articles_review')->title('На ревью')->readonly(),
                        Number::make('articles_draft')->title('Черновики')->readonly(),
                        Number::make('articles_archived')->title('В архиве')->readonly(),
                    ]),
                ]),
                Block::make('Связь с пользователями', [
                    Number::make('contact_messages')->title('Получено сообщений')->readonly(),
                ]),
                Block::make('Окружение', [
                    Columns::make([
                        Input::make('db_status')->title('БД')->readonly(),
                        Input::make('db_driver')->title('Драйвер БД')->readonly(),
                        Input::make('php_version')->title('PHP')->readonly(),
                        Input::make('app_env')->title('APP_ENV')->readonly(),
                        Input::make('snapshot_at')->title('Срез данных')->readonly(),
                    ]),
                ]),
            ]),
        ];
    }

    public function commandBar(): array
    {
        return [
            Button::make('Обновить')->method('refresh'),
            Button::make('Сбросить счётчик')
                ->method('resetContactCounter')
                ->confirm('Точно сбросить счётчик отправленных сообщений?')
                ->destructive(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        return [
            'message' => 'Снапшот обновлён',
            'refresh' => true,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function resetContactCounter(): array
    {
        Cache::forget('admin.contact.sent_total');

        return [
            'message' => 'Счётчик сообщений сброшен',
            'refresh' => true,
        ];
    }
}

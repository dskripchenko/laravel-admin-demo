# laravel-admin-demo

Демонстрационный стенд **dskripchenko/laravel-admin** — готовый проект на
Laravel 12 с подключёнными ядром и всеми восемью соседними пакетами. Нужен для
двух вещей:

1. **Витрина** — публично задеплоенный адрес (`admin-demo.example.com`), где
   админку можно потыкать, ничего не устанавливая.
2. **Шаблон быстрого старта** — `composer create-project dskripchenko/laravel-admin-demo my-admin`
   выводит разработчика в рабочую панель меньше чем за пять минут.

Один и тот же репозиторий работает в обоих режимах.

> 🌐 [English](../../README.md) · [Deutsch](../de/README.md) · **Русский** · [中文](../zh/README.md)

## Что внутри

- **Три демонстрационных ресурса** — Articles (блог с WYSIWYG), Products
  (каталог с категориями), Orders (статусы workflow).
- **Все восемь соседних пакетов включены** в `config/admin.php`:
  - `laravel-admin-starter` — системные ресурсы (Users / Roles / AuditLog / Settings / Translations / ContentBlocks)
  - `laravel-admin-health` — панель проверок состояния
  - `laravel-admin-jobs` — упавшие задачи, батчи, глубина очереди
  - `laravel-admin-media` — медиабиблиотека
  - `laravel-admin-pulse` — телеметрия (запросы, SQL, задачи, исключения)
  - `laravel-admin-search` — глобальный поиск (⌘K)
  - `laravel-admin-quill` — редактор Quill
  - `laravel-admin-tinymce` — редактор TinyMCE
- **Демо-данные**: 50 статей, 50 товаров, 50 заказов (`DemoSeeder`).
- **Учётная запись администратора**: `admin@example.com` / `password`.

## Быстрый старт (локально)

```bash
composer create-project dskripchenko/laravel-admin-demo my-admin
cd my-admin
composer setup    # install + key:gen + sqlite + migrate + seed + npm build
php artisan serve
```

Откройте [http://localhost:8000/admin](http://localhost:8000/admin) и войдите
как `admin@example.com` / `password`.

### Другой способ — клонированием

```bash
git clone git@github.com:dskripchenko/laravel-admin-demo.git
cd laravel-admin-demo
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan db:seed --class=DemoSeeder
npm install && npm run build
php artisan serve
```

## Публичный деплой

Пошаговое руководство через Laravel Forge — в
[`deploy/forge.md`](../../deploy/forge.md); вариант с самостоятельным хостингом
на Docker — в [`deploy/docker-compose.yml`](../../deploy/docker-compose.yml).

После каждого `git push origin main` Forge выполняет:

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. `php artisan migrate --force`
4. `php artisan db:seed --class=DemoSeeder` (только если в окружении выставлено `RESET=true`)

Строка cron, которая раз в сутки сбрасывает стенд, — там же, в
`deploy/forge.md`.

## Структура

```
demo/
├── app/
│   ├── Admin/Resources/        # ArticleResource, ProductResource, OrderResource
│   └── Models/                 # Article, Product, Order
├── config/
│   └── admin.php               # все восемь пакетов в plugins[], три демо-ресурса
├── database/
│   ├── migrations/             # articles + products + orders
│   └── seeders/DemoSeeder.php  # 50 + 50 + 50 сгенерированных записей
├── deploy/                     # конфигурация Docker / Forge / nginx
└── resources/, public/, ...    # обычная раскладка Laravel 12
```

## Как сделать своим

- Замените `App\Admin\Resources\*` собственными ресурсами.
- Уберите ненужные пакеты из `plugins[]` в `config/admin.php`.
- Удалите демо-миграции (`database/migrations/2026_01_01_*`) и `DemoSeeder`,
  если стартуете с чистого листа.

## Лицензия

MIT.

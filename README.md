# laravel-admin-demo

Демонстрационный стенд **dskripchenko/laravel-admin** — готовый Laravel 12-проект с подключёнными core и всеми 8 sister-pack'ами. Используется как:

1. **Showcase** — публично задеплоенный URL (`admin-demo.example.com`), куда можно зайти и потыкать админку без локальной установки.
2. **Quick-start template** — `composer create-project dskripchenko/laravel-admin-demo my-admin` для онбординга разработчика за ≤ 5 минут.

Один и тот же репозиторий работает в обоих режимах.

## Что внутри

- **3 Demo-Resource'а** — Articles (WYSIWYG-блог), Products (каталог с категориями), Orders (workflow-статусы).
- **Все 8 sister-pack'ов активированы** в `config/admin.php`:
  - `laravel-admin-starter` — system Resource'ы (Users / Roles / AuditLog / Settings / Translations / ContentBlocks)
  - `laravel-admin-health` — health-checks dashboard
  - `laravel-admin-jobs` — failed jobs / batches / queue depth
  - `laravel-admin-media` — медиа-библиотека
  - `laravel-admin-pulse` — телеметрия (request / query / job / exception)
  - `laravel-admin-search` — глобальный поиск (cmd+K)
  - `laravel-admin-quill` — WYSIWYG Quill
  - `laravel-admin-tinymce` — WYSIWYG TinyMCE
- **Демо-данные**: 50 статей, 50 товаров, 50 заказов (DemoSeeder).
- **Admin-аккаунт**: `admin@example.com` / `password`.

## Quick-start (локально)

```bash
composer create-project dskripchenko/laravel-admin-demo my-admin
cd my-admin
composer setup    # install + key:gen + sqlite + migrate + seed + npm build
php artisan serve
```

Открыть [http://localhost:8000/admin](http://localhost:8000/admin), залогиниться `admin@example.com` / `password`.

### Альтернативный вариант — клонирование

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

## Public deploy

См. [`deploy/forge.md`](deploy/forge.md) для пошагового guide через Laravel Forge или [`deploy/docker-compose.yml`](deploy/docker-compose.yml) для self-hosted Docker-варианта.

После каждого `git push origin main` Forge автоматически:
1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. `php artisan migrate --force`
4. `php artisan db:seed --class=DemoSeeder` (только при флаге `RESET=true` в env)

Кронтаб для periodic-reset стенда (раз в сутки) — см. `deploy/forge.md`.

## Структура

```
demo/
├── app/
│   ├── Admin/Resources/        # ArticleResource, ProductResource, OrderResource
│   └── Models/                 # Article, Product, Order
├── config/
│   └── admin.php               # все 8 packs в plugins[], 3 demo-resource'а
├── database/
│   ├── migrations/             # articles + products + orders
│   └── seeders/DemoSeeder.php  # 50 + 50 + 50 фейк-записей
├── deploy/                     # Docker / Forge / nginx конфиги
└── resources/, public/, ...    # стандартный Laravel 12 layout
```

## Кастомизация

- Заменить `App\Admin\Resources\*` на свои.
- Удалить ненужные packs из `config/admin.php` `plugins[]`.
- Удалить демо-миграции (`database/migrations/2026_01_01_*`) и `DemoSeeder` если стартуете на чистый.

## Лицензия

MIT.

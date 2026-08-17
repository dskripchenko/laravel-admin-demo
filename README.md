# laravel-admin-demo

A demonstration stand for **dskripchenko/laravel-admin** — a ready Laravel 12
project with the core and all eight sister packages wired up. It serves two
purposes:

1. **Showcase** — a publicly deployed URL (`admin-demo.example.com`) where the
   panel can be clicked through without installing anything.
2. **Quick-start template** — `composer create-project dskripchenko/laravel-admin-demo my-admin`
   puts a developer in a working panel in under five minutes.

The same repository works in both modes.

> 🌐 **English** · [Deutsch](docs/de/README.md) · [Русский](docs/ru/README.md) · [中文](docs/zh/README.md)

## What's inside

- **Three demo resources** — Articles (a WYSIWYG blog), Products (a catalogue
  with categories), Orders (workflow statuses).
- **The sister packages enabled** in `config/admin.php`:
  - `laravel-admin-starter` — system resources (Users / Roles / AuditLog / Settings / Translations / ContentBlocks)
  - `laravel-admin-health` — health-check dashboard
  - `laravel-admin-jobs` — failed jobs, batches
  - `laravel-admin-media` — media library
  - `laravel-admin-pulse` — telemetry (request / query / job / exception)

  Three packages were retired on 17.08.2026: the core had grown its own
  versions of what they offered. `laravel-admin-search` duplicated the core's
  global search — and, being installed, its route SHADOWED the core's, so this
  demo was showing the weaker of the two. `laravel-admin-quill` and
  `laravel-admin-tinymce` were empty shells: the editors' Vue components live
  in the core, and switching to them is a few lines in `resources/js/admin.js`
  (see the comment there).
- **Demo data**: 50 articles, 50 products, 50 orders (`DemoSeeder`).
- **Admin account**: `admin@example.com` / `password`.

## Quick start (locally)

```bash
composer create-project dskripchenko/laravel-admin-demo my-admin
cd my-admin
composer setup    # install + key:gen + sqlite + migrate + seed + npm build
php artisan serve
```

Open [http://localhost:8000/admin](http://localhost:8000/admin) and sign in with
`admin@example.com` / `password`.

### Alternatively, by cloning

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

## Public deployment

See [`deploy/forge.md`](deploy/forge.md) for a step-by-step guide through
Laravel Forge, or [`deploy/docker-compose.yml`](deploy/docker-compose.yml) for a
self-hosted Docker setup.

After every `git push origin main`, Forge runs:

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. `php artisan migrate --force`
4. `php artisan db:seed --class=DemoSeeder` (only when `RESET=true` is set in the environment)

The cron entry that resets the stand once a day is in `deploy/forge.md`.

## Layout

```
demo/
├── app/
│   ├── Admin/Resources/        # ArticleResource, ProductResource, OrderResource
│   └── Models/                 # Article, Product, Order
├── config/
│   └── admin.php               # all eight packs in plugins[], three demo resources
├── database/
│   ├── migrations/             # articles + products + orders
│   └── seeders/DemoSeeder.php  # 50 + 50 + 50 fake records
├── deploy/                     # Docker / Forge / nginx configuration
└── resources/, public/, ...    # the standard Laravel 12 layout
```

## Making it yours

- Replace `App\Admin\Resources\*` with your own resources.
- Drop the packs you don't need from `plugins[]` in `config/admin.php`.
- Delete the demo migrations (`database/migrations/2026_01_01_*`) and
  `DemoSeeder` if you are starting from a clean slate.

## License

MIT.

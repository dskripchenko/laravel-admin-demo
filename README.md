# Laravel Admin — demo and documentation site

> 🌐 **English** · [Русский](docs/ru/README.md)

The public showcase of [dskripchenko/laravel-admin](https://github.com/dskripchenko/laravel-admin):
a Laravel 13 application whose admin panel *is* the documentation site.

- **A live panel** with a demo shop (category tree, products with images,
  customers, orders with a status flow) and a blog (Markdown posts, categories,
  tags, authors) — a few thousand records, the same on every reset.
- **The documentation inside the admin.** The Markdown the package ships in
  `vendor/dskripchenko/laravel-admin/docs/{en,ru}` is rendered by
  `Layout::markdown()` in the panel's current language, with links between
  pages kept in the panel and an "Edit on GitHub" button.
- **A showcase** where every example screen shows the PHP that built it.
- **Demo mode**: one-click Administrator / Editor / Viewer accounts, read-only
  protection, a banner with a countdown to the hourly reset.
- **A landing page** at `/` (English/Russian, light/dark, plain Blade).

## Run locally

```bash
git clone https://github.com/dskripchenko/laravel-admin-demo
cd laravel-admin-demo
composer setup          # install, .env, key, SQLite, demo:reset
php artisan serve
```

Open http://localhost:8000 (landing) or http://localhost:8000/admin/login.
PHP 8.4+ with `gd`, `pdo_sqlite` and `intl`; no Node.

## Demo accounts

| Role | Email | Password | Can |
|---|---|---|---|
| Administrator | `admin@demo.test` | `demo` | Everything, including users and roles |
| Editor | `editor@demo.test` | `demo` | Catalog, blog and media only |
| Viewer | `viewer@demo.test` | `demo` | Every section, read-only (`admin.*.view`) |

The accounts and their roles are defined in `config/demo.php` and created by
`database/seeders/AccessSeeder.php`. The login page shows them as "Sign in as …"
buttons when `ADMIN_DEMO=true`. With `ADMIN_DEMO_READONLY=true` (the default)
nobody — the administrator included — can change users, roles, settings,
profiles or passwords; demo records stay editable.

## Reset

```bash
php artisan demo:reset --force
```

Wipes the generated media, runs `migrate:fresh --seed`, republishes the admin
frontend and clears the cache (about five seconds on SQLite). The seed is
deterministic; dates are relative to today, so the dashboards always look
current.

The reset runs from the Laravel scheduler (`DEMO_RESET_CRON`, hourly by default;
empty disables it), together with the health checks and the telemetry
aggregation of the sister packs (`routes/console.php`). On a server, add the
usual cron entry and a queue worker:

```cron
* * * * * cd /path/to/app && php artisan schedule:run >> /dev/null 2>&1
```

```bash
php artisan queue:work --tries=3   # under supervisord/systemd
```

The Docker image runs both for you.

## Where things are

| Path | What |
|---|---|
| `app/Providers/AdminServiceProvider.php` | Resources, screens and the whole menu |
| `app/Admin/Resources/{Shop,Blog}` | Products, categories, orders, customers; posts, categories, tags, authors |
| `app/Admin/Dashboards/ShopDashboard.php` + `app/Admin/Widgets` | The home dashboard |
| `app/Docs/DocsCatalog.php` | The documentation pages, their order and menu groups |
| `app/Docs/DocsLibrary.php` | Reads a page, falls back to English, rewrites links, caches |
| `app/Admin/Screens/Docs` | One screen per page (`/admin/screens/docs-concepts-menu`) and the contents page |
| `app/Admin/Showcase` | `ShowcaseScreen` + `ShowsSource` and the example screens |
| `app/Console/Commands/DemoResetCommand.php` | `demo:reset` |
| `app/Http/Middleware/DemoNotice.php` | The banner text in the visitor's language and the countdown |
| `resources/views/landing.blade.php` | The landing page |
| `config/demo.php` | Demo accounts, reset schedule, docs repository |
| `lang/ru.json` | Russian for every string of the site |

### Documentation pages

A page is a file under `docs/{locale}/` of the installed package. To add one,
add an entry to `DocsCatalog::PAGES` and a three-line screen class in
`app/Admin/Screens/Docs/Pages`. Relative links to pages in the catalog become
links to their screens; links to anything else (sources, pages that are not in
the catalog) open on GitHub. Prepared pages are cached until the file changes.

### Showcase screens

Extend `App\Admin\Showcase\ShowcaseScreen`, implement `demo()` and `group()`, and
add the class to `AdminServiceProvider::SHOWCASE`. The page appends a
"How it's built" block with the class's source — or one method's, via
`sourceMethod()` — read through reflection.

### Translations

Strings are written in English in the code; `lang/ru.json` holds the Russian.
The admin translates resource, field, menu and action labels through it on its
own, and the landing page uses `__()`. Add a key to `lang/ru.json` whenever you
add a visible string.

## Tests

```bash
composer test                    # PHPUnit: accounts and roles, read-only guard, docs, showcase, reset
php artisan serve &
npm install && npx playwright install chromium
npm run crawl                    # signs in as each role, opens every menu entry and docs page
```

The crawler fails on JavaScript errors, console errors and HTTP 5xx, and saves
screenshots to `tests/e2e/screenshots/`. `LANDING_SHOTS=1 npm run crawl`
refreshes the screenshots of the landing page in `public/landing/`.

## Deploy

One container: FrankenPHP serves the app; supervisord runs the scheduler and
a queue worker; SQLite lives on the `/data` volume. Every start is a reset.

```bash
cp .env.docker.example .env.docker   # set APP_URL
docker compose up -d --build
curl -f http://127.0.0.1:8080/up
```

The container listens on port 8080 (`DEMO_PORT` changes the host port) and has
a health check on `/up`. Put a TLS-terminating proxy in front of it, e.g. Caddy:

```caddyfile
admin.example.com {
    reverse_proxy 127.0.0.1:8080
}
```

Keep `TRUSTED_PROXIES=*` only while the container's port is reachable from the
proxy alone. The application key is generated on the first start and kept in
the volume; nothing else needs a secret.

## License

MIT

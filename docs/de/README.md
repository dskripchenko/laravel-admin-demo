# laravel-admin-demo

Ein Demonstrationsstand für **dskripchenko/laravel-admin** — ein fertiges
Laravel-12-Projekt mit eingebundenem Kern und allen acht Geschwisterpaketen. Es
dient zwei Zwecken:

1. **Schaufenster** — eine öffentlich bereitgestellte URL
   (`admin-demo.example.com`), auf der sich das Panel durchklicken lässt, ohne
   etwas zu installieren.
2. **Schnellstart-Vorlage** — `composer create-project dskripchenko/laravel-admin-demo my-admin`
   bringt Entwickler in weniger als fünf Minuten in ein laufendes Panel.

Dasselbe Repository funktioniert in beiden Betriebsarten.

> 🌐 [English](../../README.md) · **Deutsch** · [Русский](../ru/README.md) · [中文](../zh/README.md)

## Was enthalten ist

- **Drei Demo-Ressourcen** — Articles (ein WYSIWYG-Blog), Products (ein Katalog
  mit Kategorien), Orders (Workflow-Status).
- **Alle acht Geschwisterpakete aktiviert** in `config/admin.php`:
  - `laravel-admin-starter` — Systemressourcen (Users / Roles / AuditLog / Settings / Translations / ContentBlocks)
  - `laravel-admin-health` — Dashboard für Health-Checks
  - `laravel-admin-jobs` — fehlgeschlagene Jobs, Batches, Queue-Tiefe
  - `laravel-admin-media` — Medienbibliothek
  - `laravel-admin-pulse` — Telemetrie (Request / Query / Job / Exception)
  - `laravel-admin-search` — globale Suche (⌘K)
  - `laravel-admin-quill` — der Quill-Editor
  - `laravel-admin-tinymce` — der TinyMCE-Editor
- **Demodaten**: 50 Artikel, 50 Produkte, 50 Bestellungen (`DemoSeeder`).
- **Admin-Konto**: `admin@example.com` / `password`.

## Schnellstart (lokal)

```bash
composer create-project dskripchenko/laravel-admin-demo my-admin
cd my-admin
composer setup    # install + key:gen + sqlite + migrate + seed + npm build
php artisan serve
```

[http://localhost:8000/admin](http://localhost:8000/admin) öffnen und sich mit
`admin@example.com` / `password` anmelden.

### Alternativ per Klonen

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

## Öffentliches Deployment

Eine Schritt-für-Schritt-Anleitung über Laravel Forge steht in
[`deploy/forge.md`](../../deploy/forge.md); für den selbst gehosteten
Docker-Weg siehe [`deploy/docker-compose.yml`](../../deploy/docker-compose.yml).

Nach jedem `git push origin main` führt Forge aus:

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. `php artisan migrate --force`
4. `php artisan db:seed --class=DemoSeeder` (nur wenn in der Umgebung `RESET=true` gesetzt ist)

Der Cron-Eintrag, der den Stand einmal täglich zurücksetzt, steht ebenfalls in
`deploy/forge.md`.

## Aufbau

```
demo/
├── app/
│   ├── Admin/Resources/        # ArticleResource, ProductResource, OrderResource
│   └── Models/                 # Article, Product, Order
├── config/
│   └── admin.php               # alle acht Pakete in plugins[], drei Demo-Ressourcen
├── database/
│   ├── migrations/             # articles + products + orders
│   └── seeders/DemoSeeder.php  # 50 + 50 + 50 erzeugte Datensätze
├── deploy/                     # Konfiguration für Docker / Forge / nginx
└── resources/, public/, ...    # das übliche Laravel-12-Layout
```

## Zum eigenen Projekt machen

- `App\Admin\Resources\*` durch eigene Ressourcen ersetzen.
- Nicht benötigte Pakete aus `plugins[]` in `config/admin.php` entfernen.
- Die Demo-Migrationen (`database/migrations/2026_01_01_*`) und `DemoSeeder`
  löschen, wenn Sie auf der grünen Wiese starten.

## Lizenz

MIT.

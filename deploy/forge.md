# Deploy через Laravel Forge

## 1. Создание сайта

1. Forge → **Sites** → **Add Site**
   - Domain: `admin-demo.example.com`
   - Project Type: General PHP / Laravel
   - PHP Version: 8.5
   - Web Directory: `/public`

2. Подключить GitHub-repo `dskripchenko/laravel-admin-demo`, ветка `main`.

3. Forge сделает автоматический Quick Deploy после `git push`.

## 2. Deploy script

В Forge → Site → **Deployment** → **Deploy Script** замените на:

```bash
cd $FORGE_SITE_PATH
git pull origin $FORGE_SITE_BRANCH

$FORGE_COMPOSER install --no-dev --no-interaction --prefer-dist --optimize-autoloader

if [ -f artisan ]; then
    $FORGE_PHP artisan migrate --force
    $FORGE_PHP artisan config:cache
    $FORGE_PHP artisan route:cache
    $FORGE_PHP artisan view:cache
    $FORGE_PHP artisan queue:restart
fi

# Frontend build
npm ci --no-audit --no-fund
npm run build
```

## 3. Periodic reset (раз в сутки)

Чтобы посетители не "захламили" demo-данные, добавляем cron-job в Forge:

Forge → Server → **Scheduler** → **Add Cron Job**:
- Frequency: Custom — `0 4 * * *` (каждый день в 04:00 UTC)
- User: `forge`
- Command:

```bash
cd /home/forge/admin-demo.example.com && \
  /usr/bin/php artisan migrate:fresh --force --seed --seeder=DemoSeeder
```

> **Внимание**: `migrate:fresh` дропает ВСЕ таблицы. Если хост-проект использует общую БД с другими сайтами — выделите отдельную БД для demo.

## 4. .env

Forge → Site → **Environment**:

```env
APP_NAME="Laravel-Admin Demo"
APP_ENV=production
APP_KEY=base64:GENERATE_ME
APP_DEBUG=false
APP_URL=https://admin-demo.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_admin_demo
DB_USERNAME=forge
DB_PASSWORD=...

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

ADMIN_BRAND_NAME="Laravel-Admin Demo"
```

## 5. SSL

Forge → Site → **SSL** → **Let's Encrypt** → нажать Activate.

## 6. Queue worker (если нужен)

Forge → Server → **Daemons** → **Create Daemon**:
- Command: `php artisan queue:work --queue=default --tries=3 --timeout=120`
- User: `forge`
- Directory: `/home/forge/admin-demo.example.com`

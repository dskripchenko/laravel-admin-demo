# laravel-admin-demo

**dskripchenko/laravel-admin** 的演示站点——一个已经配好核心与全部八个配套包的
Laravel 12 项目。它有两个用途：

1. **展示** —— 一个公开部署的地址（`admin-demo.example.com`），无需本地安装即可点进后台随便试。
2. **快速开始模板** —— `composer create-project dskripchenko/laravel-admin-demo my-admin`
   能让开发者在五分钟内进入一个可用的后台。

同一个仓库同时满足这两种用法。

> 🌐 [English](../../README.md) · [Deutsch](../de/README.md) · [Русский](../ru/README.md) · **中文**

## 包含什么

- **三个演示资源** —— Articles（带 WYSIWYG 的博客）、Products（带分类的商品目录）、
  Orders（工作流状态）。
- **八个配套包全部启用**，配置见 `config/admin.php`：
  - `laravel-admin-starter` —— 系统资源（Users / Roles / AuditLog / Settings / Translations / ContentBlocks）
  - `laravel-admin-health` —— 健康检查面板
  - `laravel-admin-jobs` —— 失败任务、批次、队列积压
  - `laravel-admin-media` —— 媒体库
  - `laravel-admin-pulse` —— 遥测（请求 / 查询 / 任务 / 异常）
  - `laravel-admin-search` —— 全局搜索（⌘K）
  - `laravel-admin-quill` —— Quill 编辑器
  - `laravel-admin-tinymce` —— TinyMCE 编辑器
- **演示数据**：50 篇文章、50 件商品、50 笔订单（`DemoSeeder`）。
- **管理员账号**：`admin@example.com` / `password`。

## 快速开始（本地）

```bash
composer create-project dskripchenko/laravel-admin-demo my-admin
cd my-admin
composer setup    # install + key:gen + sqlite + migrate + seed + npm build
php artisan serve
```

打开 [http://localhost:8000/admin](http://localhost:8000/admin)，用
`admin@example.com` / `password` 登录。

### 另一种方式——克隆仓库

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

## 公开部署

通过 Laravel Forge 的逐步指南见 [`deploy/forge.md`](../../deploy/forge.md)；
自托管的 Docker 方案见 [`deploy/docker-compose.yml`](../../deploy/docker-compose.yml)。

每次 `git push origin main` 之后，Forge 会执行：

1. `composer install --no-dev --optimize-autoloader`
2. `npm ci && npm run build`
3. `php artisan migrate --force`
4. `php artisan db:seed --class=DemoSeeder`（仅当环境变量中设置了 `RESET=true`）

每天重置一次站点的 cron 配置同样在 `deploy/forge.md` 中。

## 目录结构

```
demo/
├── app/
│   ├── Admin/Resources/        # ArticleResource、ProductResource、OrderResource
│   └── Models/                 # Article、Product、Order
├── config/
│   └── admin.php               # plugins[] 中的八个包，以及三个演示资源
├── database/
│   ├── migrations/             # articles + products + orders
│   └── seeders/DemoSeeder.php  # 50 + 50 + 50 条生成数据
├── deploy/                     # Docker / Forge / nginx 配置
└── resources/, public/, ...    # 标准的 Laravel 12 布局
```

## 改成你自己的项目

- 用你自己的资源替换 `App\Admin\Resources\*`。
- 从 `config/admin.php` 的 `plugins[]` 中删掉用不到的包。
- 如果要从零开始，删除演示迁移（`database/migrations/2026_01_01_*`）与 `DemoSeeder`。

## 许可证

MIT。

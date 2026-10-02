<!doctype html>
<html lang="{{ $locale }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ __('Laravel Admin — the docs are the demo') }}</title>
<meta name="description" content="{{ __('A Laravel admin panel constructor: resources, screens and dashboards in PHP, a Vue 3 panel with no Node build. This site is the panel itself.') }}">
<meta property="og:title" content="Laravel Admin">
<meta property="og:description" content="{{ __('Laravel admin panel constructor — the docs are the demo') }}">
<meta property="og:image" content="{{ url('/landing/dashboard.jpg') }}">
<link rel="icon" href="/favicon.ico">
<link rel="alternate" hreflang="en" href="{{ url('/?lang=en') }}">
<link rel="alternate" hreflang="ru" href="{{ url('/?lang=ru') }}">
<style>
:root {
    --bg: #ffffff; --bg-soft: #f6f7fb; --card: #ffffff; --text: #0f172a; --muted: #5b6475;
    --line: #e4e7ef; --accent: #0d9488; --accent-strong: #0f766e; --accent-soft: #e6f6f4;
    --code-bg: #0f172a; --code-text: #e2e8f0; --shadow: 0 1px 2px rgba(15,23,42,.06), 0 12px 32px rgba(15,23,42,.08);
    color-scheme: light;
}
@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
        --bg: #0b1120; --bg-soft: #111a2e; --card: #121b2f; --text: #e5e9f2; --muted: #9aa4b8;
        --line: #1f2a40; --accent: #2dd4bf; --accent-strong: #5eead4; --accent-soft: #0f2f33;
        --code-bg: #060b16; --code-text: #e2e8f0; --shadow: 0 1px 2px rgba(0,0,0,.3), 0 12px 32px rgba(0,0,0,.35);
        color-scheme: dark;
    }
}
:root[data-theme="dark"] {
    --bg: #0b1120; --bg-soft: #111a2e; --card: #121b2f; --text: #e5e9f2; --muted: #9aa4b8;
    --line: #1f2a40; --accent: #2dd4bf; --accent-strong: #5eead4; --accent-soft: #0f2f33;
    --code-bg: #060b16; --code-text: #e2e8f0; --shadow: 0 1px 2px rgba(0,0,0,.3), 0 12px 32px rgba(0,0,0,.35);
    color-scheme: dark;
}
* { box-sizing: border-box; }
html { -webkit-text-size-adjust: 100%; }
body {
    margin: 0; background: var(--bg); color: var(--text);
    font: 16px/1.6 ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}
a { color: var(--accent-strong); }
code, pre { font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace; }
.wrap { max-width: 1160px; margin: 0 auto; padding: 0 16px; }
header.top { position: sticky; top: 0; z-index: 10; background: color-mix(in srgb, var(--bg) 88%, transparent); backdrop-filter: blur(8px); border-bottom: 1px solid var(--line); }
header.top .wrap { display: flex; align-items: center; gap: 16px; height: 60px; }
.brand { display: flex; align-items: center; gap: 10px; font-weight: 650; color: var(--text); text-decoration: none; }
.mark { width: 28px; height: 28px; border-radius: 7px; background: var(--text); color: var(--bg); display: grid; place-items: center; font: 600 13px/1 ui-monospace, monospace; }
nav.links { margin-left: auto; display: flex; align-items: center; gap: 4px; }
nav.links a, nav.links button { color: var(--muted); text-decoration: none; padding: 6px 10px; border-radius: 8px; font-size: 15px; background: none; border: 0; cursor: pointer; font-family: inherit; }
nav.links a:hover, nav.links button:hover { color: var(--text); background: var(--bg-soft); }
nav.links a[aria-current="true"] { color: var(--text); font-weight: 600; }
.hero { padding: 72px 0 40px; text-align: center; }
.eyebrow { display: inline-block; font-size: 13px; font-weight: 600; color: var(--accent-strong); background: var(--accent-soft); padding: 4px 12px; border-radius: 999px; }
h1 { font-size: clamp(32px, 5vw, 56px); line-height: 1.08; letter-spacing: -.02em; margin: 18px auto 16px; max-width: 900px; }
h1 em { font-style: normal; color: var(--accent); }
.lead { font-size: clamp(17px, 2vw, 20px); color: var(--muted); max-width: 720px; margin: 0 auto 28px; }
.cta { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
.btn { display: inline-flex; align-items: center; gap: 8px; padding: 12px 20px; border-radius: 10px; font-weight: 600; text-decoration: none; border: 1px solid var(--line); color: var(--text); background: var(--card); }
.btn.primary { background: var(--accent); border-color: var(--accent); color: #fff; }
:root[data-theme="dark"] .btn.primary { color: #042f2e; }
@media (prefers-color-scheme: dark) { :root:not([data-theme="light"]) .btn.primary { color: #042f2e; } }
.btn:hover { filter: brightness(1.05); }
.note { margin-top: 14px; font-size: 14px; color: var(--muted); }
.shot { margin: 44px auto 0; border-radius: 14px; overflow: hidden; border: 1px solid var(--line); box-shadow: var(--shadow); background: var(--bg-soft); }
.shot img { display: block; width: 100%; height: auto; }
section { padding: 56px 0; }
section.soft { background: var(--bg-soft); border-top: 1px solid var(--line); border-bottom: 1px solid var(--line); }
h2 { font-size: clamp(24px, 3vw, 34px); letter-spacing: -.01em; margin: 0 0 8px; }
.sub { color: var(--muted); margin: 0 0 28px; max-width: 760px; }
.grid { display: grid; gap: 16px; grid-template-columns: repeat(3, minmax(0, 1fr)); }
.card { background: var(--card); border: 1px solid var(--line); border-radius: 12px; padding: 20px; }
.card h3 { margin: 0 0 6px; font-size: 17px; }
.card p { margin: 0; color: var(--muted); font-size: 15px; }
.card .icon { width: 34px; height: 34px; border-radius: 9px; background: var(--accent-soft); color: var(--accent-strong); display: grid; place-items: center; margin-bottom: 12px; }
.card .icon svg { width: 18px; height: 18px; }
.gallery { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.gallery figure { margin: 0; }
.gallery img { width: 100%; height: auto; border-radius: 10px; border: 1px solid var(--line); display: block; }
.gallery figcaption { font-size: 14px; color: var(--muted); margin-top: 8px; }
.install { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0, 1fr)); }
pre { margin: 12px 0 0; background: var(--code-bg); color: var(--code-text); padding: 14px 16px; border-radius: 10px; overflow-x: auto; font-size: 14px; line-height: 1.55; }
pre .c { color: #7c8aa5; }
table { width: 100%; border-collapse: collapse; font-size: 15px; }
th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--line); }
th { color: var(--muted); font-weight: 600; font-size: 13px; text-transform: uppercase; letter-spacing: .04em; }
td code { background: var(--bg-soft); padding: 2px 6px; border-radius: 6px; font-size: 14px; }
.table-wrap { overflow-x: auto; background: var(--card); border: 1px solid var(--line); border-radius: 12px; }
footer { padding: 32px 0 48px; color: var(--muted); font-size: 14px; }
footer .wrap { display: flex; gap: 16px; flex-wrap: wrap; justify-content: space-between; }
footer a { color: var(--muted); }
@media (max-width: 900px) {
    .grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (max-width: 640px) {
    .grid, .gallery, .install { grid-template-columns: 1fr; }
    nav.links .hide-sm { display: none; }
    .hero { padding-top: 44px; }
}
</style>
<script>
    // Restore the visitor's explicit theme choice before the first paint.
    try { var t = localStorage.getItem('landing-theme'); if (t === 'dark' || t === 'light') document.documentElement.dataset.theme = t; } catch (e) {}
</script>
</head>
<body>
@php
    $icon = fn (string $path) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.$path.'</svg>';
    $features = [
        ['Forms', '30+ field types — relations, media, repeaters, translatable inputs, markdown and WYSIWYG — validated on the server.', '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M7 9h10M7 13h6"/>'],
        ['Grids', 'Sorting, search, filters, saved views, inline editing, bulk actions, summaries, export and import.', '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M3 15h18M9 3v18"/>'],
        ['Layouts', 'Rows, columns, tabs, blocks, accordions, modals, drawers and wizards — nested any way you like.', '<rect x="3" y="3" width="7" height="18" rx="1"/><rect x="14" y="3" width="7" height="8" rx="1"/><rect x="14" y="15" width="7" height="6" rx="1"/>'],
        ['Dashboards', 'Stats, charts, tables, heatmaps and gauges; a period switcher and per-user layouts.', '<path d="M3 3v18h18"/><path d="M7 15l4-4 3 3 5-6"/>'],
        ['Actions', 'Row, bulk and page actions with confirmations, modal forms, downloads and background jobs.', '<path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/>'],
        ['Listeners', 'Reactive forms: the server re-renders part of a form as the user types, no JavaScript to write.', '<path d="M4 12a8 8 0 0 1 14-5.3M20 12a8 8 0 0 1-14 5.3"/><path d="M18 3v4h-4M6 21v-4h4"/>'],
        ['Permissions', 'Roles with wildcard permissions, per-action gates, impersonation, 2FA and an audit log.', '<path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3z"/>'],
        ['i18n', 'The panel and your labels in any language; translatable model fields; locale switcher built in.', '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18"/>'],
        ['No Node', 'The Vue 3 panel ships prebuilt in the package. composer require, one artisan command, done.', '<path d="M5 12l5 5L20 7"/>'],
    ];
    $gallery = [
        ['orders', 'Orders: filters, badges, bulk actions and totals.'],
        ['product-form', 'A product form with a media picker.'],
        ['tree', 'A category tree, from a parent_id column.'],
        ['docs', 'This documentation, rendered by the panel.'],
    ];
@endphp
<header class="top">
    <div class="wrap">
        <a class="brand" href="/?lang={{ $locale }}"><span class="mark">&gt;_</span> Laravel Admin</a>
        <nav class="links" aria-label="{{ __('Main') }}">
            <a class="hide-sm" href="{{ $adminPath }}/login">{{ __('Demo') }}</a>
            <a class="hide-sm" href="{{ $adminPath }}/screens/docs">{{ __('Docs') }}</a>
            <a class="hide-sm" href="https://github.com/dskripchenko/laravel-admin" rel="noopener">GitHub</a>
            <a href="/?lang=en" @if($locale === 'en') aria-current="true" @endif>EN</a>
            <a href="/?lang=ru" @if($locale === 'ru') aria-current="true" @endif>RU</a>
            <button type="button" id="theme" aria-label="{{ __('Switch theme') }}" title="{{ __('Switch theme') }}">◐</button>
        </nav>
    </div>
</header>

<main>
    <div class="wrap hero">
        <span class="eyebrow">dskripchenko/laravel-admin {{ $version }}</span>
        <h1>{{ __('Laravel admin panel constructor') }} — <em>{{ __('the docs are the demo') }}</em></h1>
        <p class="lead">{{ __('Describe resources, screens and dashboards in PHP and get a Vue 3 panel without a Node build. This site is that panel: sign in with one click, try a shop and a blog, and read the documentation inside the admin it describes.') }}</p>
        <div class="cta">
            <a class="btn primary" href="{{ $adminPath }}/login">{{ __('Open live demo') }} →</a>
            <a class="btn" href="{{ $adminPath }}/screens/docs">{{ __('Docs') }}</a>
        </div>
        <p class="note">{{ __('No sign-up: pick Administrator, Editor or Viewer on the login page. The data resets every hour.') }}</p>
        <div class="shot">
            <img src="/landing/dashboard.jpg" width="1440" height="900" alt="{{ __('The shop dashboard of the demo') }}">
        </div>
    </div>

    <section class="soft">
        <div class="wrap">
            <h2>{{ __('Everything an admin needs, declared in PHP') }}</h2>
            <p class="sub">{{ __('A resource is a class with fields, columns, filters and actions; a screen is a class with a layout. The panel draws the rest.') }}</p>
            <div class="grid">
                @foreach ($features as [$title, $text, $svg])
                    <div class="card">
                        <div class="icon">{!! $icon($svg) !!}</div>
                        <h3>{{ __($title) }}</h3>
                        <p>{{ __($text) }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section>
        <div class="wrap">
            <h2>{{ __('What you will find inside') }}</h2>
            <p class="sub">{{ __('A demo shop and blog with a few thousand records, a showcase where every example shows its own source, and the full documentation.') }}</p>
            <div class="gallery">
                @foreach ($gallery as [$image, $caption])
                    <figure>
                        <img src="/landing/{{ $image }}.jpg" width="1440" height="900" loading="lazy" alt="{{ __($caption) }}">
                        <figcaption>{{ __($caption) }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </div>
    </section>

    <section class="soft">
        <div class="wrap">
            <h2>{{ __('Install') }}</h2>
            <p class="sub">{{ __('PHP 8.2+, Laravel 11, 12 or 13. The admin lives at /admin and does not touch your routes or users.') }}</p>
            <div class="install">
                <div class="card">
                    <h3>{{ __('Into an existing application') }}</h3>
                    <pre><code>composer require dskripchenko/laravel-admin
php artisan admin:install</code></pre>
                </div>
                <div class="card">
                    <h3>{{ __('As a new application') }}</h3>
                    <pre><code>composer create-project dskripchenko/laravel-admin-skeleton my-app
cd my-app &amp;&amp; php artisan serve</code></pre>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="wrap">
            <h2>{{ __('Demo accounts') }}</h2>
            <p class="sub">{{ __('Each role sees a different panel. Writes to users, roles, settings and profiles are switched off on this stand.') }}</p>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>{{ __('Role') }}</th><th>{{ __('Email') }}</th><th>{{ __('Password') }}</th><th>{{ __('Can') }}</th></tr></thead>
                    <tbody>
                    @foreach ($accounts as $account)
                        <tr>
                            <td>{{ __($account['label']) }}</td>
                            <td><code>{{ $account['email'] }}</code></td>
                            <td><code>{{ $account['password'] }}</code></td>
                            <td>{{ __($account['description']) }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <div class="cta" style="margin-top: 28px">
                <a class="btn primary" href="{{ $adminPath }}/login">{{ __('Open live demo') }} →</a>
            </div>
        </div>
    </section>
</main>

<footer>
    <div class="wrap">
        <span>MIT · <a href="https://github.com/dskripchenko/laravel-admin" rel="noopener">GitHub</a> · <a href="https://packagist.org/packages/dskripchenko/laravel-admin" rel="noopener">Packagist</a> · <a href="https://www.npmjs.com/package/@dskripchenko/laravel-admin" rel="noopener">npm</a></span>
        <span>{{ __('This site') }}: <a href="https://github.com/dskripchenko/laravel-admin-demo" rel="noopener">dskripchenko/laravel-admin-demo</a></span>
    </div>
</footer>

<script>
    document.getElementById('theme').addEventListener('click', function () {
        var root = document.documentElement;
        var dark = root.dataset.theme ? root.dataset.theme === 'dark' : matchMedia('(prefers-color-scheme: dark)').matches;
        root.dataset.theme = dark ? 'light' : 'dark';
        try { localStorage.setItem('landing-theme', root.dataset.theme); } catch (e) {}
    });
</script>
</body>
</html>

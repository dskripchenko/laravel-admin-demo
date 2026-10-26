<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="refresh" content="8">
    <title>Resetting the demo…</title>
    <style>
        :root { color-scheme: light dark; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; font: 16px/1.5 system-ui, -apple-system, sans-serif; background: #f8fafc; color: #1f2937; }
        @media (prefers-color-scheme: dark) { body { background: #0f172a; color: #e2e8f0; } }
        main { text-align: center; padding: 24px; }
        h1 { font-size: 20px; margin: 0 0 8px; }
        p { margin: 0; opacity: .75; }
        .spin { width: 28px; height: 28px; margin: 0 auto 16px; border: 3px solid currentColor; border-right-color: transparent; border-radius: 50%; animation: s 1s linear infinite; opacity: .5; }
        @keyframes s { to { transform: rotate(360deg); } }
        @media (prefers-reduced-motion: reduce) { .spin { animation: none; } }
    </style>
</head>
<body>
    <main>
        <div class="spin" aria-hidden="true"></div>
        <h1>The demo is resetting its data</h1>
        <p>It takes about twenty seconds — this page reloads by itself.</p>
        <p lang="ru">Демо обновляет данные — страница обновится сама.</p>
    </main>
</body>
</html>

/**
 * Screenshot + console-log demo admin страницы.
 *
 * Usage:
 *   node screenshot.mjs <url> <out.png>
 *   LOGIN=1 node screenshot.mjs http://127.0.0.1:8000/admin/r/articles /tmp/articles.png
 */

import { chromium } from 'playwright'

const url = process.argv[2] ?? 'http://127.0.0.1:8000/admin'
const out = process.argv[3] ?? '/tmp/admin.png'
const doLogin = process.env.LOGIN === '1'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const logs = []
page.on('console', (msg) => logs.push(`[${msg.type()}] ${msg.text()}`))
page.on('pageerror', (err) => logs.push(`[pageerror] ${err.message}`))
page.on('requestfailed', (req) => {
    const failure = req.failure()
    logs.push(`[requestfailed] ${req.url()} — ${failure?.errorText ?? 'unknown'}`)
})

if (doLogin) {
    await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
    await page.waitForSelector('input[type="email"]', { state: 'visible', timeout: 10_000 })
    await page.locator('input[type="email"]').fill('admin@example.com')
    await page.locator('input[type="password"]').fill('password')
    await page.waitForTimeout(150) // дать v-model зафиксироваться
    await Promise.all([
        page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
        page.locator('button[type="submit"]').click(),
    ])
}

await page.goto(url, { waitUntil: 'networkidle' })

// Diagnostics: bootstrap user, manifest fetch
const diag = await page.evaluate(() => {
    const bs = (globalThis).__ADMIN_BOOTSTRAP__
    return { bootstrapUser: bs?.user ?? null, manifestVersion: bs?.manifestVersion }
})
console.log('--- diag.bootstrapUser:', diag.bootstrapUser ? diag.bootstrapUser.email : null)
console.log('--- diag.manifestVersion:', diag.manifestVersion)

try {
    await page.waitForFunction(
        () => !document.body.innerText.includes('Страница не найдена'),
        { timeout: 8000, polling: 200 },
    )
} catch {
    // 404 — let it ride and reveal в screenshot.
}
await page.waitForTimeout(500)
await page.screenshot({ path: out, fullPage: true })

console.log('--- screenshot:', out)
console.log('--- title:', await page.title())
console.log('--- url:', page.url())
console.log('--- body text (first 250 chars):', (await page.locator('body').innerText()).slice(0, 250))
console.log('--- logs:')
for (const l of logs) console.log('  ', l)

await browser.close()

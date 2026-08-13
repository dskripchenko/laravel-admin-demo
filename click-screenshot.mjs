import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const logs = []
page.on('console', (msg) => logs.push(`[${msg.type()}] ${msg.text()}`))
page.on('pageerror', (err) => logs.push(`[pageerror] ${err.message}`))

// Login
await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])

// Wait until the manifest loads (the router then gets the routes for the articles).
await page.waitForFunction(
    () => {
        const cards = document.querySelectorAll('.admin-home__card')
        return cards.length >= 11
    },
    { timeout: 10_000, polling: 200 },
)
console.log('--- home loaded with', await page.locator('.admin-home__card').count(), 'cards')

// Diagnostics: which routes are in the router right now?
const routeNames = await page.evaluate(() => {
    // We make sure the router is reachable through the Vue app: we try
    // globally. When there is none we return an empty array.
    const app = (globalThis).__VUE_APP__
    return app && app._context && app._context.app
        ? []
        : []
})
console.log('--- routeNames count (might be empty if not exposed):', routeNames.length)

// An SPA navigation to the articles through a click.
const articles = page.getByRole('button', { name: /Статьи/ })
await articles.click()
await page.waitForTimeout(1500)

await page.screenshot({ path: '/tmp/admin-articles.png', fullPage: true })
console.log('--- screenshot saved')
console.log('--- url:', page.url())
console.log('--- title:', await page.title())
console.log('--- body (first 400):', (await page.locator('body').innerText()).slice(0, 400))
console.log('--- logs:')
for (const l of logs.slice(-20)) console.log('  ', l)

await browser.close()

import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const logs = []
page.on('console', (msg) => logs.push(`[${msg.type()}] ${msg.text()}`))
page.on('pageerror', (err) => logs.push(`[pageerror] ${err.message}`))
page.on('response', async (r) => {
    if (r.url().includes('/api/admin/articles/')) {
        console.log(`<- ${r.status()} ${r.url()}`)
    }
})

const target = process.argv[2] ?? 'http://127.0.0.1:8000/admin/r/articles/1/edit'
const out = process.argv[3] ?? '/tmp/admin-edit.png'

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])

await page.waitForFunction(
    () => document.querySelectorAll('.admin-home__card').length >= 11,
    { timeout: 10_000, polling: 200 },
)

await page.goto(target, { waitUntil: 'networkidle' })
// We wait for the dynamic routes to appear (the manifest loads asynchronously).
try {
    await page.waitForFunction(
        () => !document.body.innerText.includes('Страница не найдена'),
        { timeout: 8000, polling: 200 },
    )
} catch {}
await page.waitForTimeout(1500) // wait for read fetch

await page.screenshot({ path: out, fullPage: true })
console.log('--- screenshot:', out)
console.log('--- url:', page.url())
console.log('--- title:', await page.title())
console.log('--- body (first 500):', (await page.locator('body').innerText()).slice(0, 500))
console.log('--- logs:')
for (const l of logs.slice(-10)) console.log('  ', l)

await browser.close()

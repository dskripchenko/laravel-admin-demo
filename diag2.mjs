import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const requests = []
page.on('request', (r) => {
    if (r.url().includes('/api/admin/')) {
        requests.push(`${r.method()} ${r.url()}`)
    }
})
page.on('response', async (r) => {
    if (r.url().includes('/api/admin/')) {
        try {
            const body = await r.text()
            console.log(`<- ${r.status()} ${r.url()}: ${body.slice(0, 250)}`)
        } catch (e) {
            console.log(`<- ${r.status()} ${r.url()}: (body err)`)
        }
    }
})

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
console.log('=== Logged in')

await page.waitForFunction(
    () => document.querySelectorAll('.admin-home__card').length >= 11,
    { timeout: 10_000, polling: 200 },
)

console.log('\n=== Click articles ===')
await page.getByRole('button', { name: /Статьи/ }).click()
await page.waitForTimeout(3500) // wait for AJAX

console.log('\n=== Final URL:', page.url())
console.log('=== Body text (first 200):', (await page.locator('body').innerText()).slice(0, 200))

console.log('\n=== Captured requests:')
for (const r of requests) console.log(' ', r)

await browser.close()

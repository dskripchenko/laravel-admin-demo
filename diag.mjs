import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const reqs = []
page.on('request', (r) => reqs.push(`${r.method()} ${r.url()}`))
page.on('response', async (r) => {
    if (r.url().includes('/api/admin/')) {
        console.log(`<- ${r.status()} ${r.url()}`)
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
console.log('=== After login URL:', page.url())
console.log('=== Cookies:')
for (const c of await ctx.cookies()) console.log(' ', c.name, '=', c.value.slice(0, 40))

await page.waitForTimeout(2000)

console.log('\n=== Manifest fetch via page:')
const manifestResp = await page.evaluate(async () => {
    const r = await fetch('/api/admin/system/manifest', {
        credentials: 'include',
        headers: { Accept: 'application/json' },
    })
    return { status: r.status, body: (await r.text()).slice(0, 200) }
})
console.log(manifestResp)

await browser.close()

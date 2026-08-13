import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const pageErrors = []
page.on('pageerror', (err) => pageErrors.push(err.message))
page.on('console', (msg) => {
    if (msg.type() === 'error') pageErrors.push('[console] ' + msg.text())
})

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
await page.waitForTimeout(2_500)

// Go to /admin (the home page) — with no screen
console.log('after login, url=', page.url())
await page.waitForTimeout(2_000)
console.log('errors after main page:', pageErrors)

// Go to the article edit page (the resource pipeline)
await page.goto('http://127.0.0.1:8000/admin/r/articles', { waitUntil: 'networkidle' })
await page.waitForTimeout(2_000)
console.log('errors after articles index:', pageErrors)

await browser.close()

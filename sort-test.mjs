import { chromium } from 'playwright'
const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()
await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
await page.waitForTimeout(2500)
await page.goto('http://127.0.0.1:8000/admin/r/products', { waitUntil: 'networkidle' })
await page.waitForTimeout(2500)

// Click ID header 3 times
const idHeader = page.locator('th.uid-table__th--sortable').first()

// Click 1: → asc
await idHeader.click()
await page.waitForTimeout(500)
const aria1 = await idHeader.getAttribute('aria-sort')
console.log('After click 1:', aria1)

// Click 2: → desc
await idHeader.click()
await page.waitForTimeout(500)
const aria2 = await idHeader.getAttribute('aria-sort')
console.log('After click 2:', aria2)

// Click 3: → none
await idHeader.click()
await page.waitForTimeout(500)
const aria3 = await idHeader.getAttribute('aria-sort')
console.log('After click 3:', aria3)

await page.screenshot({ path: '/tmp/admin-sort-test.png', fullPage: false })
console.log('Saved screenshot')
await browser.close()

import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type=email]').fill('admin@example.com')
await page.locator('input[type=password]').fill('password')
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login')),
    page.locator('button[type=submit]').click(),
])
await page.waitForTimeout(2500)

console.log('after login:', page.url())

// Navigate to the dashboard through the menu — we expand "Аналитика" and click "Аналитика" (the dashboard)
await page.locator('aside button', { hasText: 'Аналитика' }).first().click()
await page.waitForTimeout(500)
const dashLinks = await page.locator('aside .admin-sidebar-node[data-depth="1"]').allTextContents()
console.log('analytics children:', dashLinks.map(s => s.trim()))

// We click the dashboard leaf "Аналитика" (under analytics the child is called "Аналитика" too, that is the resolved label)
const dashLink = page.locator('aside .admin-sidebar-node[data-depth="1"] a').first()
const href = await dashLink.getAttribute('href')
console.log('dashboard link href:', href)
await dashLink.click()
await page.waitForTimeout(2500)

console.log('on:', page.url())
console.log('h1:', await page.locator('h1').first().textContent().catch(() => null))
console.log('cells:', await page.locator('.admin-dashboard__cell').count())
await page.screenshot({ path: '/tmp/dash-via-menu.png', fullPage: false })

await browser.close()

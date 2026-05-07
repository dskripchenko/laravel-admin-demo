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
await page.waitForTimeout(2500) // manifest

// Все routes
const routes = await page.evaluate(() => {
    const win = globalThis
    // Trying to find Vue's router instance via app
    const app = document.getElementById('admin-app')?.__vue_app__
    if (!app) return null
    const router = app.config.globalProperties.$router
    return router?.getRoutes?.().map((r) => ({ path: r.path, name: r.name }))
})
console.log('routes:', JSON.stringify(routes, null, 2).slice(0, 2000))

await browser.close()

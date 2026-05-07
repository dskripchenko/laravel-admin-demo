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
await page.waitForTimeout(2000)

console.log('=== After login DOM check ===')
const hasLoadingBar = await page.locator('.admin-loading-bar').count()
const hasPageHost = await page.locator('.admin-page-host').count()
const hasShell = await page.locator('.uid-sidebar-layout').count()
console.log(`admin-loading-bar: ${hasLoadingBar}`)
console.log(`admin-page-host: ${hasPageHost}`)
console.log(`uid-sidebar-layout: ${hasShell}`)

// Click Articles, наблюдаем classes на enter/leave
console.log('\n=== Click Articles (sidebar) ===')
await page.evaluate(() => {
    document.querySelector('a[href*="/r/articles"]')?.click()
})

// За 50ms смотрим: есть ли .admin-page-leave-active
await page.waitForTimeout(50)
const leaveActive = await page.locator('.admin-page-leave-active').count()
const enterActive = await page.locator('.admin-page-enter-active').count()
const loadingBarVisible = await page.locator('.admin-loading-bar--visible').count()
console.log(`@50ms: leave-active=${leaveActive} enter-active=${enterActive} bar-visible=${loadingBarVisible}`)

await page.waitForTimeout(150)
const leaveActive2 = await page.locator('.admin-page-leave-active').count()
const enterActive2 = await page.locator('.admin-page-enter-active').count()
console.log(`@200ms: leave-active=${leaveActive2} enter-active=${enterActive2}`)

await page.waitForTimeout(500)
console.log(`@700ms URL:`, page.url())

await browser.close()

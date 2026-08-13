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

await page.goto('http://127.0.0.1:8000/admin/dashboard/content', { waitUntil: 'networkidle' })
await page.waitForTimeout(2_500)

// Resetting the persisted layout — POST /dashboard/reset.
await page.evaluate(async () => {
    await fetch('/api/admin/dashboard/reset', {
        method: 'POST', credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': decodeURIComponent(
                document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.slice('XSRF-TOKEN='.length) ?? '',
            ),
        },
        body: JSON.stringify({ key: 'content' }),
    })
})
await page.reload({ waitUntil: 'networkidle' })
await page.waitForTimeout(2_500)

const before = await page.locator('.admin-dashboard__cell').count()
console.log('cells before edit:', before)

await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(500)

// Remove last widget
await page.locator('.admin-widget-actions button[aria-label="Удалить"]').last().click()
await page.waitForTimeout(800)

const afterRemove = await page.locator('.admin-dashboard__cell').count()
console.log('cells after remove:', afterRemove, '(expected', before - 1, ')')

await page.screenshot({ path: '/tmp/dash-remove-after.png', fullPage: false })

// Remove two more and check the persistence
await page.locator('.admin-widget-actions button[aria-label="Удалить"]').last().click()
await page.waitForTimeout(500)
await page.locator('.admin-widget-actions button[aria-label="Удалить"]').last().click()
await page.waitForTimeout(500)
const after3 = await page.locator('.admin-dashboard__cell').count()
console.log('cells after 3 removes:', after3, '(expected', before - 3, ')')

await page.getByRole('button', { name: 'Сохранить', exact: true }).first().click()
await page.waitForTimeout(1500)

await page.reload({ waitUntil: 'networkidle' })
await page.waitForTimeout(2_500)
const persisted = await page.locator('.admin-dashboard__cell').count()
console.log('cells after reload:', persisted, '(expected', before - 3, ')')

await browser.close()

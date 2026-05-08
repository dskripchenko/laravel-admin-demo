import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const errs = []
page.on('pageerror', (e) => errs.push('[err] ' + e.message))
page.on('console', (m) => {
    if (m.type() === 'error') errs.push('[con] ' + m.text())
})
const saveRequests = []
page.on('request', (r) => {
    if (r.url().includes('/api/admin/dashboard/save')) {
        saveRequests.push({ method: r.method(), url: r.url(), data: r.postData() })
    }
})

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

// Enter edit mode
await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(500)
console.log('in edit mode:', await page.locator('.admin-dashboard__grid--editing').count() > 0)

// Сразу Save (без модификаций)
const allButtons = await page.locator('.admin-page__actions button').allTextContents()
console.log('all buttons:', allButtons.map((s) => s.trim()))
const saveBtn = page.getByRole('button', { name: 'Сохранить', exact: true }).first()
console.log('save button visible:', await saveBtn.isVisible({ timeout: 5000 }).catch(() => 'TIMEOUT'))
const isDisabled = await saveBtn.isDisabled({ timeout: 5000 }).catch(() => 'TIMEOUT')
console.log('save button disabled:', isDisabled)

await saveBtn.click({ timeout: 5000 }).catch((e) => console.log('click err:', e.message.split('\n')[0]))
await page.waitForTimeout(1500)

console.log('save requests count:', saveRequests.length)
if (saveRequests.length > 0) {
    console.log('save body:', saveRequests[0].data)
}
console.log('still in edit mode:', await page.locator('.admin-dashboard__grid--editing').count() > 0)

console.log('errors:')
for (const e of errs.filter(e => !e.includes("'length'") && !e.includes("'data' is not iterable"))) {
    console.log(' ', e.split('\n')[0])
}

await browser.close()

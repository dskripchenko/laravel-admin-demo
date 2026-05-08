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
await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(500)

const cellCount = () => page.locator('.admin-dashboard__cell').count()
const initialCount = await cellCount()
console.log('initial cells:', initialCount)

// === REMOVE last widget ===
console.log('\n=== REMOVE WIDGET ===')
await page.locator('.admin-widget-actions button[aria-label="Удалить"]').last().click()
await page.waitForTimeout(500)
const afterRemove = await cellCount()
console.log('cells after remove:', afterRemove, ' (Δ', afterRemove - initialCount, ')')

// === ADD WIDGET via dialog ===
console.log('\n=== ADD WIDGET ===')
await page.locator('button', { hasText: /Add widget/i }).first().click()
await page.waitForTimeout(500)
const dialogVisible = await page.locator('[role=dialog], .uid-modal-content').first().isVisible()
console.log('add dialog visible:', dialogVisible)

// Inspect dialog content
const dialogText = await page.locator('[role=dialog], .uid-modal-content').first().textContent()
console.log('dialog text snippet:', dialogText?.slice(0, 250))

// Try to fill type=stat (selecting from select)
const selects = await page.locator('[role=dialog] select, .uid-modal-content select').all()
console.log('selects in dialog:', selects.length)

// Try save the new widget — кнопка "Сохранить" в диалоге (НЕ та что в header)
const dialogSaveBtn = page.locator('[role=dialog] button, .uid-modal-content button').filter({ hasText: /Добавить|Сохранить/ }).last()
const saveBtnVisible = await dialogSaveBtn.isVisible().catch(() => false)
console.log('dialog save btn visible:', saveBtnVisible)
if (saveBtnVisible) {
    await dialogSaveBtn.click().catch((e) => console.log('dialog save err:', e.message))
    await page.waitForTimeout(500)
}
const afterAdd = await cellCount()
console.log('cells after add:', afterAdd, ' (Δ', afterAdd - afterRemove, ')')
await page.screenshot({ path: '/tmp/dash-full-1-after-add.png', fullPage: false })

// === Save layout ===
console.log('\n=== SAVE ===')
await page.getByRole('button', { name: 'Сохранить', exact: true }).first().click()
await page.waitForTimeout(1500)
console.log('save requests:', saveRequests.length)
if (saveRequests.length > 0) {
    const data = JSON.parse(saveRequests[0].data ?? '{}')
    console.log('saved widgets count:', data.widgets?.length, ' first 3:', data.widgets?.slice(0, 3))
}

// === Reload — проверим персистентность ===
console.log('\n=== RELOAD (persistence) ===')
await page.reload({ waitUntil: 'networkidle' })
await page.waitForTimeout(2_500)
const persistedCount = await cellCount()
console.log('cells after reload:', persistedCount)

console.log('\n=== ERRORS ===')
const novel = errs.filter(e => !e.includes("'length'") && !e.includes("'data' is not iterable"))
console.log('novel errs:', novel.length)
for (const e of novel.slice(0, 5)) console.log('  ', e.split('\n')[0])

await browser.close()

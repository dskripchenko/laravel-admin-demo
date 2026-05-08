import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const errs = []
page.on('pageerror', (e) => errs.push('[err] ' + e.message))
page.on('console', (m) => {
    if (m.type() === 'error') errs.push('[con] ' + m.text())
})

// === Track network for /dashboard/save ===
const saveRequests = []
page.on('request', (r) => {
    if (r.url().includes('/dashboard/save')) {
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

console.log('=== INITIAL VIEW ===')
const cellsBefore = await page.locator('.admin-dashboard__cell').allTextContents()
console.log('cells (truncated 30 chars each):', cellsBefore.map((s) => s.trim().slice(0, 30)))

await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(800)

console.log('\n=== EDIT MODE ===')
console.log('overlays:', await page.locator('.admin-widget-actions').count())
console.log('configure btns:', await page.locator('.admin-widget-actions button[aria-label="Настройки"]').count())
console.log('remove btns:', await page.locator('.admin-widget-actions button[aria-label="Удалить"]').count())
console.log('drag handles:', await page.locator('.admin-widget-actions button[aria-label="Перетащить"]').count())
console.log('resize handles:', await page.locator('.admin-dashboard__resize').count())

// === 1) Configure widget ===
console.log('\n=== CONFIGURE WIDGET ===')
await page.locator('.admin-widget-actions button[aria-label="Настройки"]').first().click()
await page.waitForTimeout(500)
const configDialogVisible = await page.locator('[role=dialog], .uid-modal-content').first().isVisible().catch(() => false)
console.log('config dialog visible:', configDialogVisible)
const dialogContents = await page.locator('[role=dialog], .uid-modal-content').first().textContent().catch(() => null)
console.log('dialog contents preview:', dialogContents?.slice(0, 200))
await page.screenshot({ path: '/tmp/dash-edit-1-config.png', fullPage: false })
await page.keyboard.press('Escape')
await page.waitForTimeout(300)

// === 2) Resize widget ===
console.log('\n=== RESIZE WIDGET ===')
const cells = await page.locator('.admin-dashboard__cell').all()
const beforeStyle1 = await cells[0].getAttribute('style')
console.log('cell[0] style before resize:', beforeStyle1)

const resize0 = page.locator('.admin-dashboard__resize').nth(0)
const resizeBox = await resize0.boundingBox()
if (resizeBox) {
    await page.mouse.move(resizeBox.x + 5, resizeBox.y + 5)
    await page.mouse.down()
    await page.mouse.move(resizeBox.x + 200, resizeBox.y + 5, { steps: 10 })
    await page.mouse.up()
    await page.waitForTimeout(500)
}
const afterStyle1 = await cells[0].getAttribute('style')
console.log('cell[0] style after resize:', afterStyle1)
const resizeChanged = beforeStyle1 !== afterStyle1
console.log('resize changed style:', resizeChanged)
await page.screenshot({ path: '/tmp/dash-edit-2-resized.png', fullPage: false })

// === 3) Drag widget — play with locator.dragTo() ===
console.log('\n=== DRAG WIDGET ===')
const labelsBefore = []
for (const c of await page.locator('.admin-dashboard__cell').all()) {
    const txt = await c.locator('h3, .widget-title, [class*="title"]').first().textContent().catch(() => '')
    labelsBefore.push((txt || '').trim().slice(0, 25))
}
console.log('order before drag:', labelsBefore.slice(0, 6))

const handle1 = page.locator('.admin-dashboard__cell').nth(0).locator('[data-drag-handle="true"]')
const cell3 = page.locator('.admin-dashboard__cell').nth(2)
await handle1.dragTo(cell3, { force: true })
await page.waitForTimeout(800)

const labelsAfter = []
for (const c of await page.locator('.admin-dashboard__cell').all()) {
    const txt = await c.locator('h3, .widget-title, [class*="title"]').first().textContent().catch(() => '')
    labelsAfter.push((txt || '').trim().slice(0, 25))
}
console.log('order after drag:', labelsAfter.slice(0, 6))
const dragChangedOrder = JSON.stringify(labelsBefore) !== JSON.stringify(labelsAfter)
console.log('drag changed order:', dragChangedOrder)
await page.screenshot({ path: '/tmp/dash-edit-3-drag.png', fullPage: false })

// === 4) Save ===
console.log('\n=== SAVE LAYOUT ===')
await page.locator('button', { hasText: /^Сохранить$/ }).first().click()
await page.waitForTimeout(1500)
console.log('save requests:', saveRequests.map((r) => ({ method: r.method, url: r.url, data: r.data?.slice(0, 200) })))
console.log('out of edit mode:', !(await page.locator('.admin-dashboard__grid--editing').count() > 0))
await page.screenshot({ path: '/tmp/dash-edit-4-saved.png', fullPage: false })

// === Errors ===
console.log('\n=== ERRORS ===')
const novel = errs.filter((e) => !e.includes("'length'") && !e.includes("'data' is not iterable"))
console.log('novel errs count:', novel.length)
for (const e of novel.slice(0, 5)) console.log('  ', e.split('\n')[0])

await browser.close()

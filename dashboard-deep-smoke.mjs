import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const errs = []
page.on('pageerror', (e) => errs.push('[err] ' + e.message))
page.on('console', (m) => {
    if (m.type() === 'error') errs.push('[con] ' + m.text())
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

// Reset persisted layout (чистое состояние)
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

await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(800)

// === 1. Inspect DOM state ===
console.log('=== DOM AUDIT ===')
const cellsInfo = await page.evaluate(() => {
    const cells = Array.from(document.querySelectorAll('.admin-dashboard__cell'))
    return cells.slice(0, 3).map((c, i) => ({
        idx: i,
        draggable: c.getAttribute('draggable'),
        editingClass: c.classList.contains('admin-dashboard__cell--editing'),
        gridStyle: c.getAttribute('style'),
        hasOverlay: !!c.querySelector('.admin-widget-actions'),
        hasResizeHandle: !!c.querySelector('.admin-dashboard__resize'),
        hasDragHandle: !!c.querySelector('[data-drag-handle="true"]'),
    }))
})
console.log('first 3 cells:', cellsInfo)

// === 2. Configure click — почему не открывается dialog? ===
console.log('\n=== CONFIGURE CLICK ===')
const configBtn = page.locator('.admin-widget-actions button[aria-label="Настройки"]').first()
console.log('btn visible:', await configBtn.isVisible())
console.log('btn is enabled:', await configBtn.isEnabled())

// Add click listener to verify firing
await page.evaluate(() => {
    const btn = document.querySelector('.admin-widget-actions button[aria-label="Настройки"]')
    if (btn) {
        btn.addEventListener('click', () => { window.__cfgClickFired = true }, { capture: true })
    }
})
await configBtn.click({ force: true })
await page.waitForTimeout(800)
const cfgFired = await page.evaluate(() => window.__cfgClickFired)
console.log('configure click fired:', cfgFired)
const dialogShown = await page.locator('[role=dialog], .admin-dialog').count()
console.log('dialog visible:', dialogShown > 0)
const dialogText = await page.locator('.admin-dialog').first().textContent().catch(() => null)
console.log('dialog text snippet:', dialogText?.slice(0, 100))
await page.keyboard.press('Escape')
await page.waitForTimeout(300)

// === 3. Real mouse drag — playwright high-level ===
console.log('\n=== MOUSE DRAG (real) ===')
const cell0 = page.locator('.admin-dashboard__cell').nth(0)
const cell2 = page.locator('.admin-dashboard__cell').nth(2)

const handle1 = cell0.locator('[data-drag-handle="true"]')
const before = await page.locator('.admin-dashboard__cell h3, .admin-dashboard__cell .widget-title').allTextContents()
console.log('before:', before.slice(0, 5).map(s => s.trim().slice(0, 25)))

// Native drag через playwright dragTo
await handle1.dragTo(cell2, { force: true })
await page.waitForTimeout(800)
const after = await page.locator('.admin-dashboard__cell h3, .admin-dashboard__cell .widget-title').allTextContents()
console.log('after dragTo:', after.slice(0, 5).map(s => s.trim().slice(0, 25)))
console.log('changed (mouse drag):', JSON.stringify(before) !== JSON.stringify(after))

// === 4. Inspect drag handler attachment ===
console.log('\n=== DRAG HANDLER AUDIT ===')
const dragInfo = await page.evaluate(() => {
    const cell = document.querySelector('.admin-dashboard__cell')
    if (!cell) return null
    const events = ['dragstart', 'dragover', 'drop']
    // У нас listener'ы на cell-level через @dragstart="onDragStart". Vue их binds.
    // Проверим существование getEventListeners (только в DevTools API).
    return {
        draggable: cell.getAttribute('draggable'),
        innerHTML_drag_attrs: cell.outerHTML.slice(0, 200),
    }
})
console.log('drag info:', dragInfo)

console.log('\n=== ERRORS ===')
console.log('error count:', errs.length)
for (const e of errs.slice(0, 5)) console.log(' ', e.split('\n')[0])

await browser.close()

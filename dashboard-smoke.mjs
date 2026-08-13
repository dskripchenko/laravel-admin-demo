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
await page.waitForTimeout(2000)

// Navigate to the dashboard. Through the manifest the dashboards route is
// /admin/dashboards/{slug}. We check the direct URLs.
await page.goto('http://127.0.0.1:8000/admin/dashboard/content', { waitUntil: 'networkidle' })
await page.waitForTimeout(2000)
console.log('--- on dashboard url:', page.url())
console.log('--- title text:', await page.locator('h1').first().textContent().catch(() => null))

const widgetCount = await page.locator('.admin-dashboard__cell').count()
console.log('--- widget count:', widgetCount)

await page.screenshot({ path: '/tmp/dash-1-view.png', fullPage: false })

// Try edit-mode
const editBtn = page.locator('button', { hasText: /Редактировать/ }).first()
console.log('--- edit button visible:', await editBtn.isVisible().catch(() => false))
await editBtn.click().catch((e) => console.log('--- edit click err:', e.message))
await page.waitForTimeout(800)

const editingCount = await page.locator('.admin-dashboard__grid--editing').count()
const overlayCount = await page.locator('.admin-widget-actions').count()
console.log('--- editing-grid present:', editingCount, ' overlays:', overlayCount)

await page.screenshot({ path: '/tmp/dash-2-edit.png', fullPage: false })

// Try drag handle
if (overlayCount > 0) {
    const dragHandle1 = page.locator('[data-drag-handle="true"]').first()
    const dragHandleVisible = await dragHandle1.isVisible().catch(() => false)
    console.log('--- first drag-handle visible:', dragHandleVisible)
    if (dragHandleVisible) {
        const box = await dragHandle1.boundingBox()
        console.log('--- drag-handle bbox:', box)
    }
}

// Try resize handle
const resizeHandles = await page.locator('.admin-dashboard__resize').count()
console.log('--- resize handles:', resizeHandles)

// Try +Add widget
const addBtn = page.locator('button', { hasText: /Add widget/i }).first()
console.log('--- add btn visible:', await addBtn.isVisible().catch(() => false))
await addBtn.click().catch(() => undefined)
await page.waitForTimeout(800)

// Check if dialog opened
const dialogVisible = await page.locator('[role=dialog], .uid-modal').first().isVisible().catch(() => false)
console.log('--- add dialog opened:', dialogVisible)
await page.screenshot({ path: '/tmp/dash-3-add-dialog.png', fullPage: false })

// Close dialog (Escape)
await page.keyboard.press('Escape')
await page.waitForTimeout(300)

// Try configure first widget
const configBtn = page.locator('.admin-widget-actions button', { hasText: /настроить|configure|⚙/i }).first()
const configVisible = await configBtn.isVisible().catch(() => false)
console.log('--- configure btn visible:', configVisible)

// Try drag operation by manual mouse events on first two widgets
const cells = await page.locator('.admin-dashboard__cell').all()
console.log('--- cells in DOM:', cells.length)

if (cells.length >= 2) {
    const handle1 = page.locator('.admin-dashboard__cell').nth(0).locator('[data-drag-handle="true"]').first()
    const handle2box = await page.locator('.admin-dashboard__cell').nth(2).boundingBox()
    const handle1box = await handle1.boundingBox()
    console.log('--- handle1 box:', handle1box, ' target box:', handle2box)
    if (handle1box && handle2box) {
        // An HTML5 drag-and-drop NEEDS a special playwright API:
        await handle1.hover()
        await page.mouse.down()
        await page.mouse.move(handle2box.x + handle2box.width / 2, handle2box.y + handle2box.height / 2, { steps: 12 })
        await page.mouse.up()
        await page.waitForTimeout(500)
        // Check if order changed
    }
}

console.log('--- errors count:', errs.length)
console.log('--- novel errs:')
for (const e of errs.slice(0, 10)) console.log('   ', e.split('\n')[0])

await browser.close()

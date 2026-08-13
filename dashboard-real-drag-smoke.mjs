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
await page.waitForTimeout(2500)
await page.evaluate(async () => {
    await fetch('/api/admin/dashboard/reset', {
        method: 'POST', credentials: 'include',
        headers: {
            'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
            'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find((c) => c.startsWith('XSRF-TOKEN='))?.slice('XSRF-TOKEN='.length) ?? ''),
        }, body: JSON.stringify({ key: 'content' }),
    })
})
await page.reload({ waitUntil: 'networkidle' })
await page.waitForTimeout(2500)
await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(800)

// === DRAG through real mouse events (an HTML5 drag with pointers) ===
console.log('=== DRAG (mouse + native dragstart triggered via dataTransfer) ===')
const titleOf = (sel) => page.locator(sel).first().locator('h3, .widget-title').first().textContent().catch(() => '')

const beforeOrder = await page.evaluate(() => {
    const cells = Array.from(document.querySelectorAll('.admin-dashboard__cell'))
    return cells.map(c => (c.querySelector('h3, .widget-title')?.textContent ?? '').trim().slice(0, 25))
})
console.log('before:', beforeOrder.slice(0, 5))

// Use playwright's locator.dragTo() — this fires HTML5 dragstart with proper bubbling
const handle1 = page.locator('.admin-dashboard__cell').nth(0).locator('[data-drag-handle="true"]')
const cell3 = page.locator('.admin-dashboard__cell').nth(2)
await handle1.dragTo(cell3, { force: true })
await page.waitForTimeout(800)

const afterOrder = await page.evaluate(() => {
    const cells = Array.from(document.querySelectorAll('.admin-dashboard__cell'))
    return cells.map(c => (c.querySelector('h3, .widget-title')?.textContent ?? '').trim().slice(0, 25))
})
console.log('after: ', afterOrder.slice(0, 5))
console.log('drag changed:', JSON.stringify(beforeOrder) !== JSON.stringify(afterOrder))

// === RESIZE vertical ===
console.log('\n=== RESIZE vertical ===')
const cell0 = page.locator('.admin-dashboard__cell').first()
const styleBefore = await cell0.getAttribute('style')
console.log('cell[0] style before:', styleBefore)

const resize = page.locator('.admin-dashboard__resize').first()
const rb = await resize.boundingBox()
await page.mouse.move(rb.x + 8, rb.y + 8)
await page.mouse.down()
await page.mouse.move(rb.x + 8, rb.y + 320, { steps: 12 })
await page.mouse.up()
await page.waitForTimeout(400)

const styleAfter = await cell0.getAttribute('style')
console.log('cell[0] style after: ', styleAfter)
console.log('vertical resize:', styleAfter !== styleBefore && /grid-row.*span [2-6]/.test(styleAfter ?? ''))

// === RESIZE horizontal ===
console.log('\n=== RESIZE horizontal ===')
const styleBefore2 = await cell0.getAttribute('style')
const resize2 = page.locator('.admin-dashboard__resize').first()
const rb2 = await resize2.boundingBox()
await page.mouse.move(rb2.x + 8, rb2.y + 8)
await page.mouse.down()
await page.mouse.move(rb2.x + 250, rb2.y + 8, { steps: 12 })
await page.mouse.up()
await page.waitForTimeout(400)

const styleAfter2 = await cell0.getAttribute('style')
console.log('horizontal resize:', styleAfter2 !== styleBefore2)
console.log('after horizontal:', styleAfter2)

await page.screenshot({ path: '/tmp/dash-real-drag.png', fullPage: false })
await browser.close()

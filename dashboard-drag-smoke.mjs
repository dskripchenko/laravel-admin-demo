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
await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(500)

// A native HTML5 drag through dispatchEvent — playwright's drag-to behaves
// incorrectly for draggable=true elements in Chromium.
const dragChange = await page.evaluate(() => {
    const cells = Array.from(document.querySelectorAll('.admin-dashboard__cell'))
    if (cells.length < 3) return { error: 'not enough cells' }

    const titleOf = (c) => (c.querySelector('h3, .widget-title, [class*="title"]')?.textContent ?? '').trim().slice(0, 30)
    const before = cells.map(titleOf)

    const source = cells[0]
    const target = cells[2]
    const handle = source.querySelector('[data-drag-handle="true"]')
    if (!handle) return { error: 'no drag handle' }

    const dataTransfer = new DataTransfer()
    // We emulate the native HTML5 drag cycle
    handle.dispatchEvent(new DragEvent('dragstart', { bubbles: true, cancelable: true, dataTransfer }))
    target.dispatchEvent(new DragEvent('dragover', { bubbles: true, cancelable: true, dataTransfer }))
    target.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer }))
    handle.dispatchEvent(new DragEvent('dragend', { bubbles: true, cancelable: true, dataTransfer }))

    return new Promise((resolve) => {
        requestAnimationFrame(() => {
            const after = Array.from(document.querySelectorAll('.admin-dashboard__cell')).map(titleOf)
            resolve({ before, after, changed: JSON.stringify(before) !== JSON.stringify(after) })
        })
    })
})
console.log('drag result:', dragChange)
await page.screenshot({ path: '/tmp/dash-drag-after.png', fullPage: false })

console.log('errors:')
for (const e of errs.filter(e => !e.includes("'length'") && !e.includes("'data' is not iterable"))) {
    console.log(' ', e.split('\n')[0])
}

await browser.close()

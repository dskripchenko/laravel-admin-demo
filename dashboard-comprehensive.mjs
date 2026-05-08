import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const errs = []
page.on('pageerror', (e) => errs.push('[err] ' + e.message))
page.on('console', (m) => {
    if (m.type() === 'error') errs.push('[con] ' + m.text())
})
const saveReqs = []
page.on('request', (r) => {
    if (r.url().includes('/api/admin/dashboard/save')) {
        saveReqs.push({ url: r.url(), data: r.postData() })
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

console.log('=== INITIAL ===')
const initial = await page.locator('.admin-dashboard__cell').count()
console.log('cells:', initial)

await page.locator('button', { hasText: /Редактировать/ }).first().click()
await page.waitForTimeout(500)

// 1) DRAG: переместить 1-й виджет на 3-ю позицию
console.log('\n=== DRAG ===')
const drag = await page.evaluate(() => {
    const cells = Array.from(document.querySelectorAll('.admin-dashboard__cell'))
    const titleOf = (c) => (c.querySelector('h3, .widget-title, [class*="title"]')?.textContent ?? '').trim().slice(0, 25)
    const before = cells.map(titleOf)
    const handle = cells[0].querySelector('[data-drag-handle="true"]')
    const target = cells[2]
    const dt = new DataTransfer()
    handle.dispatchEvent(new DragEvent('dragstart', { bubbles: true, cancelable: true, dataTransfer: dt }))
    target.dispatchEvent(new DragEvent('dragover', { bubbles: true, cancelable: true, dataTransfer: dt }))
    target.dispatchEvent(new DragEvent('drop', { bubbles: true, cancelable: true, dataTransfer: dt }))
    handle.dispatchEvent(new DragEvent('dragend', { bubbles: true, cancelable: true, dataTransfer: dt }))
    return new Promise((resolve) => requestAnimationFrame(() => {
        const after = Array.from(document.querySelectorAll('.admin-dashboard__cell')).map(titleOf)
        resolve({ before: before.slice(0, 5), after: after.slice(0, 5), changed: JSON.stringify(before) !== JSON.stringify(after) })
    }))
})
console.log('drag changed:', drag.changed)
console.log('  before:', drag.before)
console.log('  after: ', drag.after)

// 2) RESIZE
console.log('\n=== RESIZE first widget 3 → 6 cols ===')
const cell0 = page.locator('.admin-dashboard__cell').first()
const beforeStyle = await cell0.getAttribute('style')
const resize0 = page.locator('.admin-dashboard__resize').first()
const rb = await resize0.boundingBox()
await page.mouse.move(rb.x + 5, rb.y + 5)
await page.mouse.down()
await page.mouse.move(rb.x + 300, rb.y + 5, { steps: 10 })
await page.mouse.up()
await page.waitForTimeout(400)
const afterStyle = await cell0.getAttribute('style')
console.log('style before:', beforeStyle)
console.log('style after: ', afterStyle)

// 3) REMOVE
console.log('\n=== REMOVE last 2 ===')
await page.locator('.admin-widget-actions button[aria-label="Удалить"]').last().click()
await page.waitForTimeout(300)
await page.locator('.admin-widget-actions button[aria-label="Удалить"]').last().click()
await page.waitForTimeout(300)
const afterRemove = await page.locator('.admin-dashboard__cell').count()
console.log('cells after 2 removes:', afterRemove)

// 4) ADD widget
console.log('\n=== ADD markdown widget ===')
await page.locator('button', { hasText: /Add widget/i }).first().click()
await page.waitForTimeout(400)
await page.locator('.admin-dialog__type-card', { hasText: /^markdown$/ }).click()
await page.waitForTimeout(200)
await page.locator('.admin-dialog__field input[placeholder]').first().fill('Test note')
await page.waitForTimeout(200)
await page.getByRole('button', { name: 'Добавить', exact: true }).click()
await page.waitForTimeout(500)
const afterAdd = await page.locator('.admin-dashboard__cell').count()
console.log('cells after add:', afterAdd, '(expected', afterRemove + 1, ')')

// 5) SAVE
console.log('\n=== SAVE ===')
await page.getByRole('button', { name: 'Сохранить', exact: true }).first().click()
await page.waitForTimeout(1500)
console.log('save count:', saveReqs.length)
if (saveReqs.length > 0) {
    const data = JSON.parse(saveReqs[0].data ?? '{}')
    console.log('saved widgets count:', data.widgets?.length)
    const customWidget = data.widgets?.find((w) => String(w.slug ?? '').startsWith('custom.markdown.'))
    console.log('saved custom widget:', customWidget ? { slug: customWidget.slug, type: customWidget.type, size: customWidget.size, configKeys: Object.keys(customWidget.config ?? {}) } : null)
}

// 6) RELOAD
console.log('\n=== RELOAD persistence ===')
await page.reload({ waitUntil: 'networkidle' })
await page.waitForTimeout(2_500)
const persisted = await page.locator('.admin-dashboard__cell').count()
console.log('cells after reload:', persisted)
const titles = []
for (const c of await page.locator('.admin-dashboard__cell').all()) {
    titles.push((await c.locator('h3, .widget-title, [class*="title"]').first().textContent().catch(() => ''))?.trim()?.slice(0, 25) ?? '')
}
console.log('persisted titles (first 5):', titles.slice(0, 5))

await page.screenshot({ path: '/tmp/dash-comprehensive-final.png', fullPage: false })

console.log('\n=== ERRORS ===')
const novel = errs.filter(e => !e.includes("'length'") && !e.includes("'data' is not iterable"))
console.log('novel errors:', novel.length)
for (const e of novel.slice(0, 5)) console.log(' ', e.split('\n')[0])

await browser.close()

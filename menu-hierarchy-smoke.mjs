import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const errors = []
page.on('pageerror', (err) => errors.push(err.message))
page.on('console', (msg) => {
    if (msg.type() === 'error') errors.push('[c] ' + msg.text())
})

// Login
await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
await page.waitForTimeout(2_500)

// 1. Top-level пункты меню до раскрытия
const topLabels = await page.locator('aside .admin-sidebar-node[data-depth="0"] .uid-sidebar-item__label').allTextContents()
console.log('--- top level labels:', topLabels.map((s) => s.trim()))

// 2. Развернём «Контент» (parent с children)
const contentBtn = page.locator('aside .admin-sidebar-node[data-depth="0"] button', { hasText: /^Контент$/ }).first()
await contentBtn.click()
await page.waitForTimeout(500)

const depth1Labels = await page.locator('aside .admin-sidebar-node[data-depth="1"] .uid-sidebar-item__label').allTextContents()
console.log('--- depth=1 (after expanding Контент):', depth1Labels.map((s) => s.trim()))

// 3. Развернём «Метки» (depth=1 с children)
const tagsBtn = page.locator('aside .admin-sidebar-node[data-depth="1"] button', { hasText: /^Метки$/ }).first()
await tagsBtn.click()
await page.waitForTimeout(300)

const depth2Labels = await page.locator('aside .admin-sidebar-node[data-depth="2"] .uid-sidebar-item__label').allTextContents()
console.log('--- depth=2 labels:', depth2Labels.map((s) => s.trim()))

// 4. Tech на depth=2 — раскроем
const techBtn = page.locator('aside .admin-sidebar-node[data-depth="2"] button', { hasText: /^Tech$/ }).first()
await techBtn.click()
await page.waitForTimeout(300)

const depth3Labels = await page.locator('aside .admin-sidebar-node[data-depth="3"] .uid-sidebar-item__label').allTextContents()
console.log('--- depth=3 labels (stripe-mode):', depth3Labels.map((s) => s.trim()))

// 5. PHP на depth=3 — раскроем для проверки depth=4
const phpBtn = page.locator('aside .admin-sidebar-node[data-depth="3"] button', { hasText: /^PHP$/ }).first()
await phpBtn.click()
await page.waitForTimeout(300)

const depth4Labels = await page.locator('aside .admin-sidebar-node[data-depth="4"] .uid-sidebar-item__label').allTextContents()
console.log('--- depth=4 labels (deeper stripe):', depth4Labels.map((s) => s.trim()))

// 6. PHP → Laravel → проверим depth=5
const phpInnerBtn = page.locator('aside .admin-sidebar-node[data-depth="4"]').first()

await page.screenshot({ path: '/tmp/menu-hierarchy-1-expanded.png', fullPage: false })

// 7. Проверим indent / stripe из computed style
const debug = await page.locator('aside .admin-sidebar-node').evaluateAll((els) => {
    return els.map((el) => {
        const depth = el.getAttribute('data-depth')
        const indent = getComputedStyle(el).getPropertyValue('--admin-sidebar-indent').trim()
        const stripe = getComputedStyle(el).getPropertyValue('--admin-sidebar-stripe-alpha').trim()
        const stripeMode = el.classList.contains('admin-sidebar-node--stripe')
        return { depth, indent, stripe, stripeMode }
    }).filter((r) => r.depth !== null && r.depth !== '0')
})
console.log('--- depth/indent/stripe debug (subset):')
const seen = new Set()
for (const d of debug) {
    if (!seen.has(d.depth)) {
        console.log(' ', JSON.stringify(d))
        seen.add(d.depth)
    }
}

// 8. Кликнем на самый глубокий лист — проверим navigation
const deepLeaf = page.locator('aside .admin-sidebar-node[data-depth="4"] .uid-sidebar-item__label', { hasText: /^Vue$/ }).first()
if (await deepLeaf.count() > 0) {
    await deepLeaf.click()
    await page.waitForTimeout(800)
    console.log('--- after deep-leaf click url:', page.url())
}

await page.screenshot({ path: '/tmp/menu-hierarchy-2-deep.png', fullPage: false })

// 9. Errors summary
console.log('--- pageErrors count:', errors.length)
const novelErrors = errors.filter((e) => !e.includes('Cannot read properties of undefined') && !e.includes("'data' is not iterable"))
console.log('--- novel errors:', novelErrors.slice(0, 5))

await browser.close()

import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const logs = []
page.on('pageerror', (err) => logs.push(`[pageerror] ${err.message}`))

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])

await page.waitForTimeout(3000)
await page.locator('a, button').filter({ hasText: /^\s*Статьи\s*$/ }).first().click()
await page.waitForTimeout(2000)
await page.locator('button, a').filter({ hasText: /создать|добавить/i }).first().click()
await page.waitForTimeout(1500)

const editor = page.locator('.dsk-wysiwyg__content').first()
await editor.click()
await page.waitForTimeout(200)

// 1. Markdown shortcut → ul
await page.keyboard.type('- one', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.type('two', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.type('three', { delay: 20 })
// 2. A double Enter leaves the ul (B5a)
await page.keyboard.press('Enter')
await page.keyboard.press('Enter')
await page.keyboard.type('после списка', { delay: 20 })

const html1 = await editor.innerHTML()
console.log('--- after list+exit:')
console.log(html1)

// 3. Clearing, then checking the slash command inside an li (B5b)
await editor.click()
await page.keyboard.down('Meta'); await page.keyboard.press('a'); await page.keyboard.up('Meta')
await page.keyboard.press('Backspace')
await page.waitForTimeout(200)

await page.keyboard.type('- a', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.type('b', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.type('c', { delay: 20 })
// The caret is now in the third li. Slash → h2.
await page.keyboard.press('Home')
await page.keyboard.type('/h2', { delay: 30 })
await page.waitForTimeout(300)
await page.keyboard.press('Enter')
await page.waitForTimeout(200)

const html2 = await editor.innerHTML()
console.log('\n--- after slash-h2 inside li:')
console.log(html2)

await browser.close()
console.log('\n--- pageerrors:')
for (const l of logs) console.log('  ', l)

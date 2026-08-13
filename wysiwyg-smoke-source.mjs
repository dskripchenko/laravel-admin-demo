import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
await page.waitForTimeout(2500)
await page.locator('a, button').filter({ hasText: /^\s*Статьи\s*$/ }).first().click()
await page.waitForTimeout(1500)
await page.locator('button, a').filter({ hasText: /создать|добавить/i }).first().click()
await page.waitForTimeout(1500)

const editor = page.locator('.dsk-wysiwyg__content').first()
await editor.click()
await page.waitForTimeout(200)
await page.keyboard.type('## Привет', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.type('Это абзац с ', { delay: 20 })
await page.keyboard.down('Meta'); await page.keyboard.press('b'); await page.keyboard.up('Meta')
await page.keyboard.type('жирным', { delay: 20 })
await page.keyboard.down('Meta'); await page.keyboard.press('b'); await page.keyboard.up('Meta')
await page.keyboard.type(' текстом.', { delay: 20 })

// A screenshot before the toggle
await page.screenshot({ path: '/tmp/wysiwyg-src-1-before.png', fullPage: false })

// We press "Исходный код" (the last button of the toolbar)
const sourceBtn = page.locator('.dsk-wysiwyg-toolbar__btn').last()
const titleBefore = await sourceBtn.getAttribute('title')
console.log('--- last toolbar btn title:', titleBefore)
await sourceBtn.click()
await page.waitForTimeout(300)

const textarea = page.locator('.dsk-wysiwyg__source')
const textareaCount = await textarea.count()
console.log('--- source textarea visible:', textareaCount)
const sourceContent = await textarea.inputValue()
console.log('--- source HTML:')
console.log(sourceContent)

// A screenshot in source mode
await page.screenshot({ path: '/tmp/wysiwyg-src-2-source.png', fullPage: false })

// We edit the HTML — an <em>дополнение</em> is inserted
await textarea.click()
await page.keyboard.down('Meta'); await page.keyboard.press('a'); await page.keyboard.up('Meta')
await page.keyboard.press('Backspace')
await page.keyboard.type('<h1>Из source</h1><p>Параграф с <em>курсивом</em>.</p>', { delay: 5 })
await page.waitForTimeout(200)

// Back again
await sourceBtn.click()
await page.waitForTimeout(300)
const editorVisible = await editor.isVisible()
console.log('--- editor visible after toggle back:', editorVisible)
const html = await editor.innerHTML()
console.log('--- HTML after toggle back:')
console.log(html)

await page.screenshot({ path: '/tmp/wysiwyg-src-3-after.png', fullPage: false })
console.log('--- screenshots: /tmp/wysiwyg-src-{1,2,3}-*.png')
await browser.close()

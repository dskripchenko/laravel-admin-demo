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

const wysiwyg = page.locator('.dsk-wysiwyg').first()
const initial = await wysiwyg.boundingBox()
console.log('--- initial bbox:', initial)

// Проверим что resize: vertical применился через getComputedStyle
const computedResize = await wysiwyg.evaluate((el) => getComputedStyle(el).resize)
console.log('--- computed resize:', computedResize)

// Растягиваем — устанавливаем height напрямую (имитируем drag).
await wysiwyg.evaluate((el) => { el.style.height = '500px' })
await page.waitForTimeout(200)
const after = await wysiwyg.boundingBox()
console.log('--- after height=500px:', after)

// Ввод текста после resize — content scrollable
const editor = page.locator('.dsk-wysiwyg__content').first()
await editor.click()
for (let i = 0; i < 30; i++) {
    await page.keyboard.type(`Строка ${i+1}\n`, { delay: 5 })
}
await page.waitForTimeout(300)
await page.screenshot({ path: '/tmp/wysiwyg-resize-1-large.png', fullPage: false })

// Сжимаем
await wysiwyg.evaluate((el) => { el.style.height = '180px' })
await page.waitForTimeout(200)
const compact = await wysiwyg.boundingBox()
console.log('--- after height=180px (clamped by min):', compact)
await page.screenshot({ path: '/tmp/wysiwyg-resize-2-compact.png', fullPage: false })

console.log('--- screenshots: /tmp/wysiwyg-resize-{1,2}-*.png')
await browser.close()

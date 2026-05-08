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

// Включаем dark-тему через data-theme
await page.evaluate(() => { document.documentElement.setAttribute('data-theme', 'dark') })
await page.waitForTimeout(300)

await page.locator('a, button').filter({ hasText: /^\s*Статьи\s*$/ }).first().click()
await page.waitForTimeout(1500)
await page.locator('button, a').filter({ hasText: /создать|добавить/i }).first().click()
await page.waitForTimeout(1500)

const editor = page.locator('.dsk-wysiwyg__content').first()
await editor.click()
await page.keyboard.type('## Заголовок\nПараграф с ', { delay: 15 })
await page.keyboard.down('Meta'); await page.keyboard.press('b'); await page.keyboard.up('Meta')
await page.keyboard.type('жирным', { delay: 15 })
await page.keyboard.down('Meta'); await page.keyboard.press('b'); await page.keyboard.up('Meta')
await page.keyboard.type(' текстом.', { delay: 15 })

await page.screenshot({ path: '/tmp/wysiwyg-dark-1-editor.png', fullPage: false })

// Source mode
const sourceBtn = page.locator('.dsk-wysiwyg-toolbar__btn').last()
await sourceBtn.click()
await page.waitForTimeout(300)
await page.screenshot({ path: '/tmp/wysiwyg-dark-2-source.png', fullPage: false })

// Снимок цветов CSS-переменных wysiwyg
const colors = await page.evaluate(() => {
    const el = document.querySelector('.dsk-wysiwyg')
    if (!el) return null
    const cs = getComputedStyle(el)
    return {
        bg: cs.getPropertyValue('--dsk-wysiwyg-bg').trim(),
        fg: cs.getPropertyValue('--dsk-wysiwyg-fg').trim(),
        toolbarBg: cs.getPropertyValue('--dsk-wysiwyg-toolbar-bg').trim(),
        codeBg: cs.getPropertyValue('--dsk-wysiwyg-code-bg').trim(),
        actualBg: cs.backgroundColor,
        actualFg: cs.color,
    }
})
console.log('--- wysiwyg vars in dark theme:')
console.log(colors)
console.log('--- screenshots: /tmp/wysiwyg-dark-{1,2}-*.png')
await browser.close()

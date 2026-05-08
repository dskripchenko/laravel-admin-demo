import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const logs = []
page.on('console', (msg) => logs.push(`[${msg.type()}] ${msg.text()}`))
page.on('pageerror', (err) => logs.push(`[pageerror] ${err.message}`))

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
console.log('--- logged in')

// Дождаться появления sidebar меню
await page.waitForTimeout(3000)

// Diag: какие нав-элементы есть
const navItems = await page.locator('nav a, nav button, [role=menuitem]').allTextContents()
console.log('--- nav items:', navItems.slice(0, 30))

// Найти ссылку Статьи
const articlesLink = page.locator('a, button').filter({ hasText: /^\s*Статьи\s*$/ }).first()
const articlesCount = await articlesLink.count()
console.log('--- articles links count:', articlesCount)
if (articlesCount === 0) {
    await page.screenshot({ path: '/tmp/wysiwyg-no-menu.png', fullPage: true })
    console.log('--- screenshot: /tmp/wysiwyg-no-menu.png')
    await browser.close()
    process.exit(1)
}
await articlesLink.click()
await page.waitForTimeout(2000)
console.log('--- url after Статьи click:', page.url())

// Найти кнопку создания
const createCandidates = await page.locator('button, a').filter({ hasText: /создать|добавить|new|create/i }).all()
console.log('--- create candidates:', createCandidates.length)
for (const c of createCandidates.slice(0, 3)) console.log('     ', (await c.textContent())?.trim())
if (createCandidates.length > 0) {
    await createCandidates[0].click()
    await page.waitForTimeout(2000)
}
console.log('--- url after create click:', page.url())

try {
    await page.waitForSelector('.dsk-wysiwyg__content', { timeout: 15_000 })
} catch (e) {
    console.log('--- editor never appeared. URL:', page.url())
}

const editor = page.locator('.dsk-wysiwyg__content').first()
const found = await editor.count()
console.log('--- wysiwyg count:', found)

if (found === 0) {
    await page.screenshot({ path: '/tmp/wysiwyg-no-editor.png', fullPage: true })
    console.log('--- url:', page.url())
    console.log('--- body (first 800):', (await page.locator('body').innerText()).slice(0, 800))
    for (const l of logs.slice(-30)) console.log('  ', l)
    await browser.close()
    process.exit(1)
}

await editor.scrollIntoViewIfNeeded()
await editor.click()
await page.waitForTimeout(200)

await page.keyboard.type('Hello world ', { delay: 20 })
await page.keyboard.down('Meta'); await page.keyboard.press('b'); await page.keyboard.up('Meta')
await page.keyboard.type('bold text', { delay: 20 })
await page.keyboard.down('Meta'); await page.keyboard.press('b'); await page.keyboard.up('Meta')
await page.keyboard.type(' end.', { delay: 20 })
await page.keyboard.press('Enter')

await page.keyboard.type('## Заголовок 2', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.type('параграф под заголовком', { delay: 20 })
await page.keyboard.press('Enter')

await page.keyboard.type('- первый пункт', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.type('второй пункт', { delay: 20 })
await page.keyboard.press('Enter')
await page.keyboard.press('Enter')

await page.keyboard.type('/', { delay: 30 })
await page.waitForTimeout(300)
const slashMenu = page.locator('.dsk-wysiwyg-slash')
const slashOpen = await slashMenu.count()
console.log('--- slash-menu open after `/`:', slashOpen)
const slashItems = await page.locator('.dsk-wysiwyg-slash__item').count()
console.log('--- slash items:', slashItems)
await page.screenshot({ path: '/tmp/wysiwyg-1-slash-menu.png', fullPage: false })

await page.keyboard.press('Escape')
await page.waitForTimeout(200)
const slashAfterEsc = await slashMenu.count()
console.log('--- slash open after Esc:', slashAfterEsc)
// Esc оставляет `/` в тексте (как в Notion). Удаляем backspace'ом.
await page.keyboard.press('Backspace')
await page.waitForTimeout(100)

// /h2 фильтр
await page.keyboard.type('/h2', { delay: 30 })
await page.waitForTimeout(300)
const filteredCount = await page.locator('.dsk-wysiwyg-slash__item').count()
console.log('--- slash items after /h2:', filteredCount)
await page.keyboard.press('Enter')
await page.waitForTimeout(200)
await page.keyboard.type('заголовок из slash', { delay: 20 })
await page.keyboard.press('Enter')

await page.keyboard.type('Это inline code', { delay: 20 })
await page.keyboard.press('Enter')

await page.keyboard.type('/code', { delay: 30 })
await page.waitForTimeout(300)
await page.keyboard.press('Enter')
await page.waitForTimeout(200)
await page.keyboard.type('const x = 42', { delay: 20 })

const html = await editor.innerHTML()
console.log('\n--- editor HTML (raw DOM, with ZWSP):')
console.log(html.slice(0, 2000))

// v-model значение через formState — то, что улетит в БД (после vacuumZwsp).
const vModelValue = await page.evaluate(() => {
    const form = document.querySelector('input[name="body"], textarea[name="body"]')
    return form ? form.value : null
})
console.log('\n--- v-model body value (cleaned):')
console.log(vModelValue?.slice(0, 2000) ?? '(no hidden form input — controller.getHTML() unreachable from page.evaluate)')

await page.click('body', { position: { x: 10, y: 10 } })
await page.waitForTimeout(300)
await editor.scrollIntoViewIfNeeded()
await page.screenshot({ path: '/tmp/wysiwyg-2-result.png', fullPage: false })
console.log('\n--- screenshots: /tmp/wysiwyg-1-slash-menu.png, /tmp/wysiwyg-2-result.png')

console.log('\n--- console logs (last 20):')
for (const l of logs.slice(-20)) console.log('  ', l)

await browser.close()

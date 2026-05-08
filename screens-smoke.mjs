import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const pageErrors = []
page.on('pageerror', (err) => pageErrors.push(err.message))
page.on('console', (msg) => {
    if (msg.type() === 'error') pageErrors.push('[console] ' + msg.text())
})

// 1. Login
await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
await page.waitForTimeout(2_500)
console.log('--- after login:', page.url())

// 2. Список пунктов меню
const navItems = await page.locator('aside a, aside button').allTextContents()
console.log('--- menu items:', navItems.map((s) => s.trim()).filter(Boolean).slice(0, 25))

// 3. Перейти на ContactScreen напрямую через URL
await page.goto('http://127.0.0.1:8000/admin/screens/contact', { waitUntil: 'networkidle' })
await page.waitForTimeout(1_500)
console.log('--- contact screen url:', page.url())
const contactTitle = await page.locator('h1').first().textContent().catch(() => null)
console.log('--- contact title:', contactTitle)

await page.screenshot({ path: '/tmp/screen-contact-1-empty.png', fullPage: false })

// 4. Заполнить форму
await page.locator('input[type="text"]').first().fill('Денис тестировщик')
await page.locator('input[type="email"]').fill('test@example.com')
const subjectSelect = page.locator('select').first()
await subjectSelect.selectOption('bug').catch(async () => {
    // если select это UidSelect (custom) — кликаем option напрямую
    await page.locator('text="Тема"').click({ force: true }).catch(() => undefined)
})
await page.locator('textarea').fill('Это тестовое сообщение из smoke-теста, длиннее 10 символов.')
await page.waitForTimeout(300)
await page.screenshot({ path: '/tmp/screen-contact-2-filled.png', fullPage: false })

// 5. Submit
const submitBtn = page.getByRole('button', { name: /Отправить/i }).first()
await submitBtn.click()
await page.waitForTimeout(1_500)

// 6. Проверить, что появилось сообщение об успехе
const alertText = await page.locator('[role="alert"], [role="status"]').allTextContents()
console.log('--- alert(s) after send:', alertText)
await page.screenshot({ path: '/tmp/screen-contact-3-success.png', fullPage: false })

// 7. Проверить, что поля очистились
const emailValue = await page.locator('input[type="email"]').inputValue()
console.log('--- email after send (should be empty):', JSON.stringify(emailValue))

// 8. Перейти на SystemStatusScreen
await page.goto('http://127.0.0.1:8000/admin/screens/system-status', { waitUntil: 'networkidle' })
await page.waitForTimeout(1_500)
console.log('--- system-status url:', page.url())

const statusTitle = await page.locator('h1').first().textContent().catch(() => null)
console.log('--- system-status title:', statusTitle)
await page.screenshot({ path: '/tmp/screen-status-1.png', fullPage: false })

// 9. Page errors
console.log('--- pageErrors:', pageErrors.slice(0, 10))

await browser.close()

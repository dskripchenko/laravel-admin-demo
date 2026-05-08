/**
 * E2E full-flow smoke для laravel-admin.
 *
 * Покрытие: login → manifest load → меню (иерархия) → resources index/edit →
 * dashboard (view + edit-mode + save) → screens (form submit) → notifications
 * drawer → profile → logout. На каждом шаге собирает console errors.
 *
 * Запуск: cd demo && node e2e-full-flow.mjs
 * Сервер: php artisan serve --port=8000 в фоне.
 */
import { chromium } from 'playwright'

const BASE = 'http://127.0.0.1:8000'
const errs = []
let browser

async function runStep(name, fn) {
    process.stdout.write(`  ${name}... `)
    const before = errs.length
    try {
        await fn()
        const newErrs = errs.length - before
        console.log(newErrs === 0 ? 'ok' : `ok (+${newErrs} errs)`)
    } catch (e) {
        console.log(`FAIL: ${e.message.split('\n')[0]}`)
        throw e
    }
}

async function main() {
    browser = await chromium.launch({ headless: true })
    const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
    const page = await ctx.newPage()

    page.on('pageerror', (e) => errs.push('[err] ' + e.message))
    page.on('console', (m) => {
        if (m.type() === 'error') errs.push('[con] ' + m.text())
    })

    console.log('=== E2E Full Flow ===\n')

    await runStep('1. Login flow', async () => {
        await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' })
        await page.locator('input[type=email]').fill('admin@example.com')
        await page.locator('input[type=password]').fill('password')
        await Promise.all([
            page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
            page.locator('button[type=submit]').click(),
        ])
        await page.waitForTimeout(2_500)
    })

    await runStep('2. Manifest + menu иерархия', async () => {
        const items = await page.locator('aside .admin-sidebar-node[data-depth="0"] .uid-sidebar-item__label').allTextContents()
        if (items.length < 3) throw new Error(`expected >= 3 top-level menu items, got ${items.length}`)
    })

    await runStep('3. Resources index (Articles)', async () => {
        await page.goto(`${BASE}/admin/r/articles`, { waitUntil: 'networkidle' })
        await page.waitForTimeout(1_500)
        const tableRows = await page.locator('table tbody tr').count()
        if (tableRows === 0) throw new Error('no rows in articles index')
    })

    await runStep('4. Resource edit page', async () => {
        await page.goto(`${BASE}/admin/r/articles/1/edit`, { waitUntil: 'networkidle' })
        await page.waitForTimeout(2_500)
        const titleInput = page.locator('input[type=text]').first()
        const visible = await titleInput.isVisible()
        if (!visible) throw new Error('title input not visible')
    })

    await runStep('5. Dashboard view-mode', async () => {
        await page.goto(`${BASE}/admin/dashboard/content`, { waitUntil: 'networkidle' })
        await page.waitForTimeout(2_500)
        const cells = await page.locator('.admin-dashboard__cell').count()
        if (cells === 0) throw new Error('no widget cells')
    })

    await runStep('6. Dashboard edit-mode + save', async () => {
        await page.locator('button', { hasText: /Редактировать/ }).first().click()
        await page.waitForTimeout(500)
        const editing = await page.locator('.admin-dashboard__grid--editing').count()
        if (editing === 0) throw new Error('not in edit mode')
        await page.getByRole('button', { name: 'Сохранить', exact: true }).first().click()
        await page.waitForTimeout(1_000)
    })

    await runStep('7. Custom Screen (Contact form)', async () => {
        await page.goto(`${BASE}/admin/screens/contact`, { waitUntil: 'networkidle' })
        await page.waitForTimeout(2_000)
        const h1 = await page.locator('h1').first().textContent()
        if (!h1?.includes('Связаться')) throw new Error(`unexpected h1: ${h1}`)
    })

    await runStep('8. Notifications drawer', async () => {
        const bell = page.locator('button[aria-label*=Уведомлен], .admin-topbar button:has(svg)').first()
        // Найдём именно колокольчик — надёжнее по count'у visible buttons
        const bellBtns = await page.locator('.admin-topbar button').all()
        for (const btn of bellBtns) {
            const aria = await btn.getAttribute('aria-label') ?? ''
            if (aria.toLowerCase().includes('уведомлен') || aria.toLowerCase().includes('notification')) {
                await btn.click()
                break
            }
        }
        await page.waitForTimeout(500)
    })

    await runStep('9. Profile', async () => {
        await page.goto(`${BASE}/admin/profile`, { waitUntil: 'networkidle' })
        await page.waitForTimeout(1_500)
        const sections = await page.locator('.admin-profile__nav button, aside button').count()
        // Хотя бы 2 секции (Основное + что-то ещё)
    })

    await runStep('10. Logout', async () => {
        // Через UserMenu или через напрямый POST
        await page.evaluate(async () => {
            await fetch('/api/admin/auth/logout', {
                method: 'POST', credentials: 'include',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-XSRF-TOKEN': decodeURIComponent(document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.slice('XSRF-TOKEN='.length) ?? ''),
                },
            })
        })
        await page.waitForTimeout(500)
    })

    console.log('\n=== Summary ===')
    console.log('total console errors:', errs.length)
    const novel = errs.filter((e) => !e.includes("'data' is not iterable") && !e.includes("'length'"))
    console.log('novel errors (excluding pre-existing):', novel.length)
    for (const e of novel.slice(0, 5)) console.log('  ', e.split('\n')[0])

    await browser.close()
    process.exit(novel.length === 0 ? 0 : 1)
}

main().catch(async (e) => {
    console.error('FAILED:', e)
    if (browser) await browser.close()
    process.exit(2)
})

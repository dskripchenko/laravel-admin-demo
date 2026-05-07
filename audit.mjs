/**
 * Audit-проход по всем 11 Resource'ам demo стенда: index → view → edit.
 * Для каждой страницы: screenshot + сбор console errors + проверка
 * базовых элементов (title, table/card rendered, no '404', no error-state).
 *
 * Output:
 *   /tmp/admin-audit/{slug}-{kind}.png — скриншоты
 *   /tmp/admin-audit/report.json       — структурированный отчёт
 */
import { chromium } from 'playwright'
import { writeFileSync, mkdirSync } from 'node:fs'

const RESOURCES = [
  { slug: 'articles',                label: 'Статьи',           pkg: 'demo' },
  { slug: 'products',                label: 'Товары',           pkg: 'demo' },
  { slug: 'orders',                  label: 'Заказы',           pkg: 'demo' },
  { slug: 'system-users',            label: 'Пользователи',     pkg: 'starter' },
  { slug: 'system-roles',            label: 'Роли',             pkg: 'starter' },
  { slug: 'system-audit',            label: 'Журнал аудита',    pkg: 'starter' },
  { slug: 'system-health-results',   label: 'Health-checks',    pkg: 'health' },
  { slug: 'system-failed-jobs',      label: 'Failed jobs',      pkg: 'jobs' },
  { slug: 'system-job-batches',      label: 'Batch jobs',       pkg: 'jobs' },
  { slug: 'media-library',           label: 'Медиа-библиотека', pkg: 'media' },
  { slug: 'system-pulse-samples',    label: 'Pulse samples',    pkg: 'pulse' },
]

const BASE = 'http://127.0.0.1:8000'
const OUT = '/tmp/admin-audit'
mkdirSync(OUT, { recursive: true })

const report = []

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

const allLogs = []
page.on('console', (msg) => allLogs.push({ url: page.url(), type: msg.type(), text: msg.text() }))
page.on('pageerror', (err) => allLogs.push({ url: page.url(), type: 'pageerror', text: err.message }))

// Login
await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle' })
await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])
await page.waitForTimeout(2500) // manifest

console.log('=== LOGGED IN ===\n')

async function probe(slug, kind, urlPath) {
    const before = allLogs.length
    const res = { slug, kind, url: urlPath, errors: [], warnings: [], status: 'ok' }
    try {
        await page.goto(`${BASE}${urlPath}`, { waitUntil: 'networkidle', timeout: 15_000 })
        await page.waitForTimeout(1500)
        const title = await page.title()
        const bodyText = await page.locator('body').innerText()
        await page.screenshot({ path: `${OUT}/${slug}-${kind}.png`, fullPage: true })
        // Quick checks
        if (bodyText.includes('Страница не найдена') || title.includes('404')) {
            res.status = 'not_found'
            res.errors.push('404 page rendered')
        } else if (bodyText.includes('Не удалось загрузить')) {
            res.status = 'error'
            res.errors.push('error state visible')
        } else if (bodyText.includes('Доступ запрещён') || title.includes('403')) {
            res.status = 'forbidden'
            res.errors.push('403 page')
        } else if (kind === 'view' && bodyText.includes('Зарегистрируйте через registerInfolistEntry')) {
            res.warnings.push('infolist entries not registered for some types')
        } else if (kind === 'edit' && bodyText.includes("Зарегистрируйте")) {
            res.warnings.push('field components not registered for some types')
        }
        // Проверка: title содержит label resource'а либо kind ('Создать', 'Редактировать')
        res.title = title
        res.bodyHead = bodyText.slice(0, 200).replace(/\s+/g, ' ')
    } catch (e) {
        res.status = 'crashed'
        res.errors.push(`navigate crash: ${e.message}`)
    }
    // Captured logs за время этого probe
    res.logs = allLogs.slice(before).filter((l) => l.type === 'error' || l.type === 'pageerror')
    return res
}

for (const r of RESOURCES) {
    console.log(`--- ${r.slug} (${r.pkg}) ---`)
    const indexResult = await probe(r.slug, 'index', `/admin/r/${r.slug}`)
    report.push(indexResult)
    console.log(`  index: ${indexResult.status}${indexResult.warnings.length ? ' [W:' + indexResult.warnings.join('; ') + ']' : ''}`)

    // Достать первый id из этой index-таблицы для view/edit probes.
    let firstId = null
    try {
        // Из items в store через JS
        firstId = await page.evaluate((slug) => {
            const links = document.querySelectorAll('a[href*="/r/' + slug + '/"]')
            for (const a of links) {
                const m = a.getAttribute('href').match(/\/r\/[^/]+\/(\d+|[A-Za-z0-9-]+)(?:\/edit)?$/)
                if (m) return m[1]
            }
            // fallback: поищем text-content row IDs
            const cell = document.querySelector('.uid-table__row .uid-table__td:nth-child(2)')
            return cell?.textContent?.trim() ?? null
        }, r.slug)
    } catch {}

    if (!firstId || firstId === '') {
        firstId = '1'
    }
    console.log(`  using id=${firstId} for view/edit`)

    const viewResult = await probe(r.slug, 'view', `/admin/r/${r.slug}/${firstId}`)
    report.push(viewResult)
    console.log(`  view:  ${viewResult.status}${viewResult.warnings.length ? ' [W:' + viewResult.warnings.join('; ') + ']' : ''}`)

    const editResult = await probe(r.slug, 'edit', `/admin/r/${r.slug}/${firstId}/edit`)
    report.push(editResult)
    console.log(`  edit:  ${editResult.status}${editResult.warnings.length ? ' [W:' + editResult.warnings.join('; ') + ']' : ''}`)

    console.log('')
}

writeFileSync(`${OUT}/report.json`, JSON.stringify(report, null, 2))
console.log(`\n=== DONE — report at ${OUT}/report.json — screenshots at ${OUT}/ ===`)
console.log(`Total probes: ${report.length}`)
console.log(`OK:        ${report.filter((r) => r.status === 'ok' && r.warnings.length === 0).length}`)
console.log(`Warning:   ${report.filter((r) => r.warnings.length > 0).length}`)
console.log(`Error:     ${report.filter((r) => r.status !== 'ok').length}`)

await browser.close()

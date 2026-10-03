/**
 * Crawls the demo stand the way a visitor would: signs in with the one-click
 * demo button of every role, opens every menu entry (the documentation pages
 * included) and a few record pages, and fails on JavaScript errors, console
 * errors and HTTP responses >= 500. Screenshots land in tests/e2e/screenshots;
 * the landing page uses some of them (public/landing/).
 *
 *   php artisan serve &
 *   npx playwright install chromium      # once
 *   node tests/e2e/crawl.mjs
 *
 * Environment:
 *   BASE_URL           http://127.0.0.1:8000
 *   ROLES              admin,editor,viewer
 *   SCREENSHOTS        tests/e2e/screenshots
 *   LANDING_SHOTS      1 also writes the landing screenshots (light and dark) to public/landing/
 *   PLAYWRIGHT_MODULE  path of the playwright module (default: "playwright")
 *   CHROMIUM_PATH      a chromium executable to use instead of the bundled one
 */
import { mkdirSync, writeFileSync } from 'node:fs'
import { join } from 'node:path'

const { chromium } = await import(process.env.PLAYWRIGHT_MODULE ?? 'playwright')

const BASE = (process.env.BASE_URL ?? 'http://127.0.0.1:8000').replace(/\/$/, '')
const ADMIN = `${BASE}/admin`
const ROLES = (process.env.ROLES ?? 'admin,editor,viewer').split(',').map((r) => r.trim()).filter(Boolean)
const SHOTS = process.env.SCREENSHOTS ?? 'tests/e2e/screenshots'
const LANDING = process.env.LANDING_SHOTS === '1'
const ACCOUNTS = { admin: 'admin@demo.test', editor: 'editor@demo.test', viewer: 'viewer@demo.test' }

/** Pages captured for the landing page, by role and admin path. */
const LANDING_SHOTS = {
  admin: {
    '/dashboard/main': 'dashboard',
    '/r/orders': 'orders',
    '/r/product-categories': 'tree',
    '/r/products/1/edit': 'product-form',
    '/screens/docs-concepts-resources': 'docs',
    '/screens/showcase-forms-basics': 'showcase',
    '/screens/showcase-dashboards-charts': 'charts',
  },
}

mkdirSync(SHOTS, { recursive: true })

const browser = await chromium.launch({
  headless: true,
  ...(process.env.CHROMIUM_PATH ? { executablePath: process.env.CHROMIUM_PATH } : {}),
})

const problems = []
const visited = []

function watch(page, role, current) {
  page.on('pageerror', (err) => problems.push({ role, url: current(), kind: 'pageerror', detail: err.message }))
  page.on('console', (msg) => {
    if (msg.type() !== 'error') return
    // A guest's login page asks who is signed in and gets 401: expected.
    if (current().endsWith('/login') && msg.text().includes('status of 401')) return
    problems.push({ role, url: current(), kind: 'console', detail: msg.text() })
  })
  page.on('response', (res) => {
    if (res.status() >= 500) problems.push({ role, url: current(), kind: `http ${res.status()}`, detail: res.url() })
  })
}

/** The panel's permission check: `*` in a granted key matches anything, dots included. */
function allowed(granted, required) {
  if (!Array.isArray(required) || required.length === 0) return true
  return required.some((key) => granted.some((mask) => {
    if (mask === key) return true
    if (!mask.includes('*')) return false
    const re = new RegExp(`^${mask.split('*').map((p) => p.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')).join('.*')}$`)
    return re.test(key)
  }))
}

/** Every URL of the menu the user is shown (filtered like the SPA does), without duplicates. */
function menuUrls(items, granted, out = new Set()) {
  for (const item of items ?? []) {
    if (!allowed(granted, item.permissions)) continue
    if (typeof item.url === 'string' && item.url.startsWith('/')) out.add(item.url.split('#')[0])
    menuUrls(item.children, granted, out)
  }
  return out
}

async function settle(page) {
  await page.waitForLoadState('networkidle', { timeout: 15000 }).catch(() => {})
  // Charts and the markdown renderer finish a frame after the data arrives.
  await page.waitForTimeout(400)
}

// The login page with the demo buttons.
{
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } })
  const page = await context.newPage()
  let current = `${ADMIN}/login`
  watch(page, 'guest', () => current)
  await page.goto(current)
  await page.waitForSelector('[data-testid="login-demo"]', { timeout: 15000 })
  await settle(page)
  await page.screenshot({ path: join(SHOTS, 'login.png') })
  visited.push({ role: 'guest', url: current })
  await context.close()
}

for (const role of ROLES) {
  const email = ACCOUNTS[role]
  if (!email) throw new Error(`Unknown role ${role}`)
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 }, locale: 'en-US' })
  const page = await context.newPage()
  let current = `${ADMIN}/login`
  watch(page, role, () => current)

  await page.goto(current)
  const login = page.waitForResponse((res) => res.url().includes('/auth/login'))
  await page.click(`[data-testid="login-demo-${email}"]`)
  const granted = (await (await login).json())?.payload?.permissions ?? []
  await page.waitForURL((url) => !url.pathname.endsWith('/login'), { timeout: 20000 })
  await settle(page)

  const menu = await page.evaluate(async () => {
    const res = await fetch('/api/admin/system/menu', { headers: { Accept: 'application/json' }, credentials: 'same-origin' })
    return res.json()
  })
  const urls = [...menuUrls(menu?.payload?.items ?? menu?.items, granted)]
  if (role === 'admin') urls.push('/r/products/1/edit', '/r/orders/1', '/r/posts/1/edit', '/r/customers/1', '/profile')
  if (urls.length < 5) problems.push({ role, url: '/api/admin/system/menu', kind: 'menu', detail: `only ${urls.length} entries` })

  for (const path of urls) {
    current = `${ADMIN}${path}`
    const res = await page.goto(current)
    if (!res || res.status() >= 400) {
      problems.push({ role, url: current, kind: `http ${res?.status()}`, detail: 'navigation' })
      continue
    }
    await settle(page)
    // The SPA's own 404 page — not a docs page that mentions the status code.
    const notFound = await page.locator('.admin-status-page__code', { hasText: '404' }).count()
    if (notFound > 0) problems.push({ role, url: current, kind: '404', detail: 'the SPA showed its 404 page' })
    const forbidden = page.url().endsWith('/forbidden')
    if (forbidden) problems.push({ role, url: current, kind: '403', detail: 'redirected to /forbidden' })

    const name = `${role}${path.replace(/[^a-z0-9]+/gi, '-')}`.replace(/-$/, '')
    await page.screenshot({ path: join(SHOTS, `${name}.png`) })
    const landingName = LANDING_SHOTS[role]?.[path]
    if (landingName && LANDING) {
      // The demo-stand banner ("data resets every hour") belongs to this
      // installation, not to the product the landing page shows: leave it out
      // of the pictures, and give the shell back the height it reserved.
      await page.evaluate(() => {
        document.getElementById('admin-notice')?.remove()
        document.documentElement.style.setProperty('--admin-banner-height', '0px')
      })
      // No hover state left over from the crawl on the picture.
      await page.mouse.move(0, 0)
      await page.waitForTimeout(200)
      // Both themes: the landing page shows the one matching the visitor's.
      mkdirSync('public/landing', { recursive: true })
      await page.screenshot({ path: join('public/landing', `${landingName}.jpg`), type: 'jpeg', quality: 80 })
      await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'dark'))
      await page.waitForTimeout(300)
      await page.screenshot({ path: join('public/landing', `${landingName}-dark.jpg`), type: 'jpeg', quality: 80 })
      await page.evaluate(() => document.documentElement.setAttribute('data-theme', 'light'))
    }
    visited.push({ role, url: current })
  }
  await context.close()
}

// The public landing page, light and dark, in both languages — last, so the
// screenshots it shows already exist.
{
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } })
  const page = await context.newPage()
  let current = `${BASE}/`
  watch(page, 'guest', () => current)
  for (const [suffix, scheme, query] of [['en', 'light', '?lang=en'], ['en-dark', 'dark', '?lang=en'], ['ru', 'light', '?lang=ru']]) {
    await page.emulateMedia({ colorScheme: scheme })
    current = `${BASE}/${query}`
    const res = await page.goto(current)
    if (!res || res.status() >= 400) problems.push({ role: 'guest', url: current, kind: `http ${res?.status()}`, detail: 'landing' })
    await settle(page)
    await page.screenshot({ path: join(SHOTS, `landing-${suffix}.png`), fullPage: true })
    visited.push({ role: 'guest', url: current })
  }
  await context.close()
}

await browser.close()

writeFileSync(join(SHOTS, 'report.json'), JSON.stringify({ visited, problems }, null, 2))
console.log(`Visited ${visited.length} pages (${ROLES.join(', ')} + guest).`)
if (problems.length > 0) {
  console.error(`${problems.length} problem(s):`)
  for (const p of problems) console.error(`  [${p.role}] ${p.kind} ${p.url} — ${p.detail}`)
  process.exit(1)
}
console.log('No JS errors, console errors or 5xx responses.')

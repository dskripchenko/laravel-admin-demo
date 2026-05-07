import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })

const before = await ctx.cookies()
console.log('=== Before login XSRF:', before.find((c) => c.name === 'XSRF-TOKEN')?.value.slice(0, 30))

await page.locator('input[type="email"]').fill('admin@example.com')
await page.locator('input[type="password"]').fill('password')
await page.waitForTimeout(150)

await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login'), { timeout: 15_000 }),
    page.locator('button[type="submit"]').click(),
])

const after = await ctx.cookies()
console.log('=== After login XSRF:', after.find((c) => c.name === 'XSRF-TOKEN')?.value.slice(0, 30))

// Проверим что header в search pose отправит правильный XSRF.
await page.waitForTimeout(500)
const sent = await page.evaluate(async () => {
    const xsrf = document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1]
    return xsrf?.slice(0, 30)
})
console.log('=== document.cookie XSRF:', sent)

// Manually fetch with proper headers — work?
const r = await page.evaluate(async () => {
    const xsrf = document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1]
    const decoded = decodeURIComponent(xsrf ?? '')
    const resp = await fetch('/api/admin/articles/search', {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'X-XSRF-TOKEN': decoded,
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ page: 1, per_page: 3 }),
    })
    const body = await resp.text()
    return { status: resp.status, body: body.slice(0, 200) }
})
console.log('=== Manual fetch with cookie XSRF:', r)

await browser.close()

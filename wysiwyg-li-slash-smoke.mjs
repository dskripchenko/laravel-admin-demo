import { chromium } from 'playwright'

const browser = await chromium.launch({ headless: true })
const ctx = await browser.newContext({ viewport: { width: 1440, height: 900 } })
const page = await ctx.newPage()

await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle' })
await page.locator('input[type=email]').fill('admin@example.com')
await page.locator('input[type=password]').fill('password')
await Promise.all([
    page.waitForURL((u) => !u.pathname.endsWith('/login')),
    page.locator('button[type=submit]').click(),
])
await page.waitForTimeout(2000)

await page.goto('http://127.0.0.1:8000/admin/r/articles/1/edit', { waitUntil: 'networkidle' })
await page.waitForTimeout(3000)
const editor = page.locator('.dsk-wysiwyg__content[contenteditable="true"]').first()
const found = await editor.count()
console.log('editor count:', found)
if (found === 0) {
  console.log('--- url:', page.url(), 'h1:', await page.locator('h1').first().textContent().catch(()=>null))
  await browser.close()
  process.exit(0)
}
await editor.scrollIntoViewIfNeeded()
await editor.click()
await page.waitForTimeout(300)

// We clear the content and type a bullet list
await page.keyboard.press('Control+A')
await page.keyboard.press('Delete')
await page.waitForTimeout(200)
await page.keyboard.type('- item one', { delay: 30 })
await page.waitForTimeout(300)
// The markdown shortcut '- ' creates an li, so the input must now be inside the <li>
const inLi = await page.evaluate(() => {
  const sel = window.getSelection()
  if (!sel || sel.rangeCount === 0) return null
  let n = sel.getRangeAt(0).startContainer
  while (n && n.nodeType !== 1) n = n.parentElement
  let cur = n
  while (cur) {
    if (cur.tagName === 'LI') return 'LI'
    if (cur.tagName === 'UL' || cur.tagName === 'OL') return cur.tagName
    cur = cur.parentElement
  }
  return null
})
console.log('caret context after "- item one":', inLi)

// Now Enter (a new li), then "/h2" — the slash menu must open
await page.keyboard.press('Enter')
await page.waitForTimeout(200)
await page.keyboard.type('/h2', { delay: 30 })
await page.waitForTimeout(500)

const menuVisible = await page.locator('.dsk-wysiwyg-slash').isVisible().catch(() => false)
const menuText = await page.locator('.dsk-wysiwyg-slash').textContent().catch(() => null)
console.log('slash-menu visible inside <li>:', menuVisible)
console.log('menu text:', menuText?.slice(0, 200))

if (menuVisible) {
  // We choose h2 (Enter)
  await page.keyboard.press('Enter')
  await page.waitForTimeout(400)
  const html = await page.locator('.dsk-wysiwyg__content').innerHTML()
  console.log('html after /h2 select:')
  console.log(html.slice(0, 500))
}

await browser.close()

import { SourceMapConsumer } from 'source-map'
import { readFileSync } from 'node:fs'

const map = JSON.parse(readFileSync('public/build/assets/admin-dUjbKWKO.js.map', 'utf8'))
const consumer = await new SourceMapConsumer(map)

const stacks = [
    { name: 'length-error fn', line: 1, column: 164802 },
    { name: 'length-error proxy', line: 1, column: 165954 },
    { name: 'data-not-iter fn1', line: 2, column: 55856 },
    { name: 'data-not-iter fn2', line: 2, column: 55947 },
    { name: 'data-not-iter proxy', line: 2, column: 56055 },
]

for (const s of stacks) {
    const orig = consumer.originalPositionFor({ line: s.line, column: s.column })
    console.log(`${s.name}: ${orig.source}:${orig.line}:${orig.column} (${orig.name ?? ''})`)
}

consumer.destroy()

// Smoke tests against a running local site. Usage: BASE=http://localhost:8088 node tests/check.mjs
const BASE = process.env.BASE || 'http://localhost:8088';
import { readFileSync, existsSync } from 'node:fs';
const results = []; let failed = 0;
const ok = (c, m) => { results.push((c ? 'PASS ' : 'FAIL ') + m); if (!c) failed++; };
const paths = ['/', '/nazafat-manzel-tehran/', '/nazafat-fori-tehran/', '/nazafat-rahpele-tehran/', '/nazafat-daftar-sherkat-tehran/', '/loolekesh-fori-tehran/', '/loolekesh-sayyar-tehran/', '/loolebazkoni-tehran/', '/nasht-yabi-loole-tehran/', '/gheymat-nazafat-manzel/', '/nazafatchi-khanom-tehran/', '/tehran/', '/tehran/enghelab/', '/tehran/felestin/', '/tehran/yousefabad/', '/tehran/hafte-tir/', '/tehran/kargar/', '/tehran/keshavarz/', '/tehran/jomhuri/', '/darbare-ma/', '/tamas/', '/blog/', '/blog/gheymat-nazafat-manzel-tehran-1405/', '/blog/nazafatchi-khoob-chetor-entekhab-konim/', '/blog/cheklist-eib-loolekeshi-va-ghaymat-orjans/', '/faq/', '/wp-sitemap.xml', '/robots.txt'];
const strip = (s) => s.replace(/<[^>]+>/g, '').trim();

for (const p of paths) {
  const r = await fetch(BASE + p, { redirect: 'manual' });
  const html = await r.text();
  ok(r.status === 200, `${r.status} ${p}`);
  if (!/\.(xml|txt)$/.test(p) && r.status === 200) {
    const h1 = (html.match(/<h1[\s>]/g) || []).length;
    ok(h1 === 1, `  one <h1> (${h1}) ${p}`);
    const title = (html.match(/<title>([^<]*)<\/title>/) || [])[1] || '';
    const desc = (html.match(/<meta name="description" content="([^"]*)"/) || [])[1] || '';
    ok(title.length > 10, `  title (${[...title].length} chars) ${p}`);
    ok(desc.length > 40 || p.startsWith('/blog/') && desc.length > 0, `  meta description (${[...desc].length} chars) ${p}`);
    ok(/<html[^>]*dir="rtl"/.test(html), `  rtl ${p}`);
    ok(!/<script[^>]*src="[^"]*jquery/i.test(html), `  no jquery ${p}`);
    const blocks = [...html.matchAll(/<script type="application\/ld\+json">([\s\S]*?)<\/script>/g)];
    ok(blocks.length === 1, `  exactly one JSON-LD block (${blocks.length}) ${p}`);
    let types = [];
    for (const b of blocks) {
      try { const j = JSON.parse(b[1]); (j['@graph'] || [j]).forEach((n) => types.push([].concat(n['@type']).join('/'))); ok(true, `  JSON-LD valid: ${types.join(', ')} ${p}`); }
      catch (e) { ok(false, `  JSON-LD INVALID ${p}: ${e.message}`); }
    }
    const imgs = [...html.matchAll(/<img[^>]*>/g)].filter((m) => !/alt=/.test(m[0]));
    ok(imgs.length === 0, `  imgs have alt ${p}`);
  }
}
// 404 + alias redirects
let r = await fetch(BASE + '/no-such-page/', { redirect: 'manual' }); ok(r.status === 404, `404 status for unknown URL (${r.status})`);
for (const [from, to] of [['/نظافت-منزل-تهران/', '/nazafat-manzel-tehran/'], ['/تهران/انقلاب/', '/tehran/enghelab/'], ['/قیمت-نظافت-منزل/', null]]) {
  r = await fetch(BASE + encodeURI(from), { redirect: 'manual' });
  if (to) ok(r.status === 301 && (r.headers.get('location') || '').endsWith(to), `301 ${from} -> ${r.headers.get('location')}`);
}
// Service page content checks
const svc = await (await fetch(BASE + '/nazafat-manzel-tehran/')).text();
ok(/نظافت منزل تهران<\/h1>/.test(svc), 'service H1 = main keyword');
ok(/قیمت نمونه/.test(svc), 'service price marked as sample');
ok(!/آدرس|"address"/.test(svc.match(/application\/ld\+json">([\s\S]*?)<\/script>/)[1]), 'schema has no address');
ok(/areaServed/.test(svc), 'schema has areaServed');
const hood = await (await fetch(BASE + '/tehran/enghelab/')).text();
ok(['nazafat-manzel-tehran', 'nazafat-daftar-sherkat-tehran', 'loolekesh-fori-tehran'].every((s) => hood.includes(`/${s}/`)), 'neighborhood links to its 3 services');
const reverse = await (await fetch(BASE + '/loolekesh-fori-tehran/')).text();
ok(reverse.includes('/tehran/enghelab/'), 'service page lists linked neighborhoods (two-way relation)');
const price = await (await fetch(BASE + '/gheymat-nazafat-manzel/')).text();
ok(/آخرین به‌روزرسانی قیمت/.test(price) && /<time datetime="\d{4}-\d{2}-\d{2}">/.test(price), 'price page shows updated date');
ok(/<table class="price-table">/.test(price), 'price table present');

// Form API
const nonce = (await (await fetch(BASE + '/wp-json/khooneyar/v1/nonce')).json()).nonce;
ok(!!nonce, 'fresh nonce endpoint');
const post = (body) => fetch(BASE + '/wp-json/khooneyar/v1/order', { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });
const svcId = (svc.match(/name="khy_service"[\s\S]*?<option value="(\d+)"/) || [])[1];
const good = { khy_nonce: nonce, khy_name: 'علی رضایی', khy_phone: '۰۹۱۲۳۴۵۶۷۸۹', khy_service: svcId, khy_area: '0', khy_time: 'today', khy_note: 'تست خودکار', khy_source: BASE + '/' };
r = await post({ ...good, khy_nonce: 'bad' }); ok(r.status === 403, `bad nonce rejected (${r.status})`);
r = await post({ ...good, khy_phone: '12345' }); let j = await r.json(); ok(r.status === 422 && j.errors.khy_phone, `invalid phone -> 422 + Persian error: ${j.errors && j.errors.khy_phone}`);
r = await post({ ...good, khy_name: '', khy_service: '' }); j = await r.json(); ok(r.status === 422 && j.errors.khy_name && j.errors.khy_service, 'missing name/service -> field errors');
r = await post({ ...good, khy_website: 'http://spam' }); j = await r.json(); ok(r.status === 200 && j.code === 'KH-0000', 'honeypot silently accepted, not stored');
r = await post(good); j = await r.json(); ok(r.status === 200 && j.ok && /^KH-\d+$/.test(j.code) && j.wa_url.startsWith('https://wa.me/989000000000?text='), `valid order stored: ${j.code}`);
r = await post(good); j = await r.json(); ok(r.status === 429, `duplicate phone within 5 min -> 429 (${r.status})`);
// rate limit: different phones until blocked
let blocked = false;
for (let i = 0; i < 6; i++) { r = await post({ ...good, khy_phone: '0935000000' + i }); if (r.status === 429) { blocked = true; break; } }
ok(blocked, 'per-IP rate limit kicks in');
r = await post({ ...good, khy_phone: '09121111111' }); // may be blocked
// debug log
const log = '/workspace/khooneyar/wp/wp-content/debug.log';
const logText = existsSync(log) ? readFileSync(log, 'utf8') : '';
ok(logText.trim() === '', 'wp-content/debug.log empty (no PHP notices/warnings)' + (logText ? '\n' + logText.slice(0, 1500) : ''));
console.log(results.join('\n')); console.log(`\n${results.length - failed}/${results.length} passed`);
process.exit(failed ? 1 : 0);

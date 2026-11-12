// Mobile + desktop screenshots. Needs puppeteer-core (set PUPPETEER_PATH) and Chrome.
import { createRequire } from 'node:module';
const require = createRequire(process.env.PUPPETEER_PATH || '/workspace/tools/pg/node_modules/');
const puppeteer = require('puppeteer-core');
const BASE = process.env.BASE || 'http://localhost:8088';
const OUT = new URL('../screenshots/', import.meta.url).pathname;
const browser = await puppeteer.launch({ executablePath: '/usr/bin/google-chrome', headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
const targets = { home: '/', service: '/nazafat-manzel-tehran/', price: '/gheymat-nazafat-manzel/', neighborhood: '/tehran/enghelab/' };
const devices = { mobile: { width: 390, height: 844, deviceScaleFactor: 2, isMobile: true, hasTouch: true }, desktop: { width: 1366, height: 900, deviceScaleFactor: 1 } };
for (const [dn, vp] of Object.entries(devices)) {
  const page = await browser.newPage(); await page.setViewport(vp);
  const errors = []; page.on('console', (m) => { if (['error', 'warning'].includes(m.type())) errors.push(m.text()); }); page.on('pageerror', (e) => errors.push(String(e)));
  for (const [name, path] of Object.entries(targets)) {
    await page.goto(BASE + path, { waitUntil: 'networkidle0' });
    await page.screenshot({ path: `${OUT}${name}-${dn}.png`, fullPage: true });
  }
  // form success state on the service page
  await page.goto(BASE + '/nazafat-manzel-tehran/', { waitUntil: 'networkidle0' });
  const form = await page.$('form[data-khy-form]');
  await page.type('form[data-khy-form] input[name=khy_name]', 'مریم احمدی');
  await page.type('form[data-khy-form] input[name=khy_phone]', dn === 'mobile' ? '۰۹۱۲۳۴۵۶۷۹۰' : '09121234591');
  await page.select('form[data-khy-form] select[name=khy_area]', await page.$eval('select[name=khy_area] option:nth-child(2)', (o) => o.value));
  await page.click('form[data-khy-form] button[type=submit]');
  await page.waitForSelector('[data-khy-success]:not([hidden])', { timeout: 10000 });
  await new Promise((r) => setTimeout(r, 400));
  const box = await (await page.$('.order-box')).boundingBox();
  await page.evaluate(() => document.querySelector('.order-box').scrollIntoView({ block: 'start' }));
  await new Promise((r) => setTimeout(r, 200));
  await page.screenshot({ path: `${OUT}form-success-${dn}.png` });
  const wa = await page.$eval('[data-khy-wa]', (a) => a.href);
  console.log(dn, 'WA link:', decodeURIComponent(wa).replace(/\n/g, ' | '));
  // validation error state (fresh page)
  await page.goto(BASE + '/tamas/', { waitUntil: 'networkidle0' });
  await page.type('form[data-khy-form] input[name=khy_phone]', '123');
  await page.click('form[data-khy-form] button[type=submit]');
  await new Promise((r) => setTimeout(r, 300));
  await page.evaluate(() => document.querySelector('.order-box').scrollIntoView({ block: 'center' }));
  await page.screenshot({ path: `${OUT}form-errors-${dn}.png` });
  // horizontal overflow check
  for (const [name, path] of Object.entries(targets)) {
    await page.goto(BASE + path, { waitUntil: 'networkidle0' });
    const o = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    console.log(dn, name, 'horizontal overflow px:', o);
  }
  console.log(dn, 'console errors/warnings:', errors.length ? errors : 'none');
  await page.close();
}
await browser.close();

// Design-pass screenshots + overflow check. Usage: node tests/design-shots.mjs before|after [extraPath...]
import { createRequire } from 'node:module';
const require = createRequire(process.env.PUPPETEER_PATH || '/workspace/tools/pg/node_modules/');
const puppeteer = require('puppeteer-core');
const BASE = process.env.BASE || 'http://localhost:8088';
const tag = process.argv[2] || 'after';
const OUT = new URL('../shots/design-pass/', import.meta.url).pathname;
const targets = JSON.parse(process.env.TARGETS || JSON.stringify({ service: '/nazafat-manzel-tehran/', neighborhood: '/tehran/enghelab/', post: '/blog/gheymat-nazafat-manzel-tehran-1405/', tehran: '/tehran/' }));
const devices = { mobile: { width: 360, height: 800, deviceScaleFactor: 2, isMobile: true, hasTouch: true }, desktop: { width: 1280, height: 900, deviceScaleFactor: 1 } };
const browser = await puppeteer.launch({ executablePath: '/usr/bin/google-chrome', headless: 'new', args: ['--no-sandbox', '--disable-gpu'] });
for (const [dn, vp] of Object.entries(devices)) {
  const page = await browser.newPage(); await page.setViewport(vp);
  for (const [name, path] of Object.entries(targets)) {
    await page.goto(BASE + path, { waitUntil: 'networkidle0' });
    const ov = await page.evaluate(() => document.documentElement.scrollWidth - document.documentElement.clientWidth);
    await page.screenshot({ path: `${OUT}${name}-${dn}-${tag}.png`, fullPage: true });
    console.log(dn, name, 'h-overflow px:', ov);
  }
}
await browser.close();

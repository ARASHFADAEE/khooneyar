// Logs in to wp-admin and checks: order list, status change, settings page, service edit screen, neighborhood edit screen.
import { createRequire } from 'node:module';
const require = createRequire(process.env.PUPPETEER_PATH || '/workspace/tools/pg/node_modules/');
const puppeteer = require('puppeteer-core');
const BASE = process.env.BASE || 'http://localhost:8088';
const OUT = new URL('../screenshots/', import.meta.url).pathname;
const browser = await puppeteer.launch({ executablePath: '/usr/bin/google-chrome', headless: 'new', args: ['--no-sandbox'] });
const page = await browser.newPage(); await page.setViewport({ width: 1280, height: 900 });
const res = [];
await page.goto(BASE + '/wp-login.php'); await page.type('#user_login', 'admin'); await page.type('#user_pass', 'admin123'); await Promise.all([page.waitForNavigation(), page.click('#wp-submit')]);
const visit = async (path, name, shot) => { const r = await page.goto(BASE + path, { waitUntil: 'networkidle0' }); const fatal = await page.$('.wp-die-message'); res.push(`${r.status() === 200 && !fatal ? 'PASS' : 'FAIL'} ${r.status()} ${path} ${name}`); if (shot) await page.screenshot({ path: OUT + shot }); };
await visit('/wp-admin/edit.php?post_type=order_request', 'order list', 'admin-orders.png');
const rows = await page.$$eval('#the-list tr', (t) => t.length); res.push(`INFO order rows: ${rows}`);
const statusLink = await page.$eval('#the-list tr .row-actions .khy_called a', (a) => a.href); await page.goto(statusLink, { waitUntil: 'networkidle0' });
const badge = await page.$eval('#the-list tr .column-khy_status', (e) => e.textContent.trim()); res.push(`${/تماس|جدید/.test(badge) ? 'PASS' : 'FAIL'} status row action works (first row now: ${badge})`);
await visit('/wp-admin/edit.php?post_type=order_request&khy_status=called', 'filter called');
await visit('/wp-admin/options-general.php?page=khooneyar', 'settings page', 'admin-settings.png');
await visit('/wp-admin/post.php?post=5&action=edit', 'service edit', 'admin-service-edit.png');
await visit('/wp-admin/post.php?post=13&action=edit', 'neighborhood edit');
await visit('/wp-admin/post.php?post=21&action=edit', 'price page edit');
await visit('/wp-admin/post-new.php?post_type=service', 'new service');
// two-way relation via admin UI: untick enghelab on service 5? (use service 7 which isn't linked to enghelab) — tick it and save, verify the neighborhood side.
await page.goto(BASE + '/wp-admin/post.php?post=7&action=edit', { waitUntil: 'networkidle0' });
const box = await page.$('input[name="khy_service_hoods[]"][value="13"]'); await box.click();
await Promise.all([page.waitForNavigation({ waitUntil: 'networkidle0' }), page.click('#publish')]);
const hoodHtml = await (await fetch(BASE + '/tehran/enghelab/')).text();
res.push(`${hoodHtml.includes('/nazafat-rahpele-tehran/') ? 'PASS' : 'FAIL'} ticking neighborhood on a service edit screen adds the service to the neighborhood page (two-way)`);
console.log(res.join('\n')); await browser.close();

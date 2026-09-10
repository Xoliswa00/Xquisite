// Self-contained intro video: title card, guided tour of the seeded "Marigold"
// demo with burned-in captions + lower-third labels, end card — all composited
// live in the browser during capture. Output: one silent .webm, no post-work.
//
// Run:  node tests/Browser/DemoCapture/intro-baked.mjs

import { chromium } from 'playwright';
import { resolve } from 'path';

const BASE  = process.env.DEMO_BASE || 'http://127.0.0.1:8791';
const OUT   = process.env.CAP_OUT  || resolve('storage/app/demo-captures');
const EMAIL = 'demo@xquisite.co.za';
const PASS  = 'demo1234';

const pace = (ms) => new Promise((r) => setTimeout(r, ms));

// Overlay layer (caption pill + full-screen card), re-created on every navigation.
// document.documentElement can be null at the instant an init script fires (it runs
// before the parser has necessarily built the tree yet), so boot() retries until it
// exists instead of assuming it's there.
const OVERLAY = `
(() => {
  if (window.__ov) return;
  window.__ov = true;
  function boot() {
    var root = document.documentElement;
    if (!root) { setTimeout(boot, 0); return; }
    var mk = (id, css) => { var e = document.createElement('div'); e.id = id; e.style.cssText = css; root.appendChild(e); return e; };
    var cap = mk('__cap',
      "position:fixed;left:28px;bottom:28px;z-index:2147483646;background:rgba(8,12,20,.82);" +
      "color:#fff;font:500 17px/1.35 'Segoe UI',system-ui,sans-serif;padding:10px 16px;border-radius:8px;" +
      "border-left:3px solid #0078D4;opacity:0;transition:opacity .45s ease;max-width:64ch;pointer-events:none");
    var card = mk('__card',
      "position:fixed;inset:0;z-index:2147483647;background:#0b1220;color:#fff;display:flex;flex-direction:column;" +
      "align-items:center;justify-content:center;text-align:center;font-family:'Segoe UI',system-ui,sans-serif;" +
      "opacity:0;transition:opacity .5s ease;pointer-events:none");
    window.__cap = (t) => { cap.textContent = t || ''; cap.style.opacity = t ? '1' : '0'; };
    window.__card = (h) => { card.innerHTML = h || ''; card.style.opacity = h ? '1' : '0'; };
    window.__ready = true;
  }
  boot();
})();
`;

async function ensureOverlay(page) {
  await page.evaluate(OVERLAY).catch(() => {});
  await page.waitForFunction(() => window.__ready === true, { timeout: 5000 }).catch(() => {});
}
async function caption(page, t) { await ensureOverlay(page); await page.evaluate((x) => window.__cap(x), t).catch(() => {}); }
async function card(page, h)    { await ensureOverlay(page); await page.evaluate((x) => window.__card(x), h).catch(() => {}); }

const TITLE = `
  <div style="font-size:44px;font-weight:600;letter-spacing:.3px">Marigold Hair &amp; Beauty</div>
  <div style="width:44px;height:3px;background:#0078D4;margin:22px 0"></div>
  <div style="font-size:20px;color:#93c5fd">One studio. One login.</div>`;
const ENDCARD = `
  <div style="font-size:34px;font-weight:600;letter-spacing:.3px">Xquisite Creations Suite</div>
  <div style="width:44px;height:3px;background:#D4AF37;margin:22px 0"></div>
  <div style="font-size:18px;color:#9fb3c8">xquisite.brightfinance-x.co.za</div>`;

async function cleanChrome(page) {
  await page.evaluate(() => {
    // hide the "exploring a live demo" banner and any floating WhatsApp widget
    for (const el of document.querySelectorAll('body *')) {
      const t = (el.textContent || '').trim();
      if (t.startsWith('Demo Mode') && t.includes('live demo') && el.children.length < 8) { el.style.display = 'none'; }
    }
    document.querySelectorAll('a[href*="wa.me"],a[href*="whatsapp"],[class*="whatsapp"]').forEach((e) => {
      const r = e.getBoundingClientRect();
      if (r.width < 120 && r.bottom > window.innerHeight - 160) e.style.display = 'none';
    });
  }).catch(() => {});
}

async function creep(page, steps = 5, dy = 240, wait = 620) {
  for (let i = 0; i < steps; i++) { await page.mouse.wheel(0, dy); await pace(wait); }
  await pace(900);
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'smooth' })).catch(() => {});
  await pace(600);
}

async function scene(page, path, label, { hold = 2600, scroll = 5 } = {}) {
  try { await page.goto(BASE + path, { waitUntil: 'networkidle', timeout: 15000 }); }
  catch { await page.goto(BASE + path, { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {}); }
  await ensureOverlay(page);
  await cleanChrome(page);
  await pace(400);
  await caption(page, label);
  await pace(hold);
  if (scroll) await creep(page, scroll);
  await caption(page, '');
  await pace(350);
}

const TOUR = [
  ['/dashboard',           'One place for the whole business', { scroll: 4 }],
  ['/appointments',        "Bookings and the day's schedule",  { scroll: 5 }],
  ['/pos',                 'The front counter',                { hold: 3200, scroll: 3 }],
  ['/products',            'Stock levels, live',               { scroll: 4 }],
  ['/orders',              'Online store orders',              { scroll: 4 }],
  ['/orders/21',           'Every order, start to finish',     { scroll: 3 }],
  ['/leases/18',           'Property, leases and rent',        { scroll: 6 }],
  ['/clients/38/messages', 'Client conversations in one thread',{ scroll: 3 }],
];

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1280, height: 800 },
  deviceScaleFactor: 2,
  recordVideo: { dir: OUT, size: { width: 1280, height: 800 } },
});
const page = await context.newPage();
await page.addInitScript(OVERLAY);

// ── Title card over the (skipped) login ──────────────────────────────────
await page.goto(BASE + '/login', { waitUntil: 'domcontentloaded' });
await card(page, TITLE);
await pace(2800);
await page.fill('input[name="email"]', EMAIL);
await page.fill('input[name="password"]', PASS);
await Promise.all([
  page.waitForLoadState('networkidle').catch(() => {}),
  page.click('button[type="submit"], button:has-text("Sign in"), button:has-text("Log in")'),
]);
await pace(1800);
if (!page.url().includes('/dashboard')) {
  console.error('LOGIN FAILED —', page.url());
  await context.close(); await browser.close(); process.exit(1);
}
await ensureOverlay(page);
await cleanChrome(page);
await card(page, '');          // reveal the dashboard
await pace(1200);

// ── Tour ────────────────────────────────────────────────────────────────
for (const [path, label, opts] of TOUR) {
  console.log('scene', path);
  await scene(page, path, label, opts);
}

// ── End card ────────────────────────────────────────────────────────────
await card(page, ENDCARD);
await pace(2800);

await context.close();
await browser.close();
const saved = await page.video()?.path().catch(() => null);
console.log('video:', saved || `(check ${OUT})`);

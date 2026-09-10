// Paced navigation tour of the seeded "Marigold" demo tenant, recorded to webm.
// Run:  node tests/Browser/DemoCapture/intro-tour.mjs
// Output: <scratchpad>/captures/*.webm  (one file for the whole session)

import { chromium } from 'playwright';
import { resolve } from 'path';

const BASE = process.env.DEMO_BASE || 'http://127.0.0.1:8791';
const OUT  = process.env.CAP_OUT  || resolve('storage/app/demo-captures');
const EMAIL = 'demo@xquisite.co.za';
const PASS  = 'demo1234';

const pace = (ms) => new Promise((r) => setTimeout(r, ms));

async function creep(page, steps = 6, dy = 220, wait = 650) {
  for (let i = 0; i < steps; i++) {
    await page.mouse.wheel(0, dy);
    await pace(wait);
  }
  await pace(1600); // hold on the lower part of the page
  await page.evaluate(() => window.scrollTo({ top: 0, behavior: 'smooth' }));
  await pace(700);
}

async function go(page, path, { hold = 3500, scroll = 6 } = {}) {
  try {
    await page.goto(BASE + path, { waitUntil: 'networkidle', timeout: 15000 });
  } catch {
    await page.goto(BASE + path, { waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => {});
  }
  await pace(hold);
  if (scroll) await creep(page, scroll);
}

const tour = [
  ['/dashboard',          { hold: 3200, scroll: 4 }],
  ['/appointments',       { hold: 3200, scroll: 5 }],
  ['/pos',                { hold: 4000, scroll: 3 }],
  ['/products',           { hold: 2800, scroll: 4 }],
  ['/orders',             { hold: 3000, scroll: 4 }],
  ['/orders/21',          { hold: 3500, scroll: 3 }],
  ['/leases/18',          { hold: 3500, scroll: 8 }],  // Room A — R450 outstanding in the ledger
  ['/clients/38/messages',{ hold: 4500, scroll: 3 }],  // Thandeka thread
];

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1280, height: 800 },
  deviceScaleFactor: 2,
  recordVideo: { dir: OUT, size: { width: 1280, height: 800 } },
});
const page = await context.newPage();

// ── Login ────────────────────────────────────────────────────────────────
await page.goto(BASE + '/login', { waitUntil: 'networkidle' });
await pace(600);
await page.fill('input[name="email"]', EMAIL);
await pace(400);
await page.fill('input[name="password"]', PASS);
await pace(500);
await Promise.all([
  page.waitForLoadState('networkidle').catch(() => {}),
  page.click('button[type="submit"], button:has-text("Sign in"), button:has-text("Log in")'),
]);
await pace(2500);

if (!page.url().includes('/dashboard')) {
  console.error('LOGIN FAILED — still at', page.url());
  await context.close();
  await browser.close();
  process.exit(1);
}

// ── Tour ─────────────────────────────────────────────────────────────────
for (const [path, opts] of tour) {
  console.log('visiting', path);
  await go(page, path, opts);
}

await pace(800);
await context.close();
await browser.close();

const saved = await page.video()?.path().catch(() => null);
console.log('video:', saved || `(check ${OUT})`);

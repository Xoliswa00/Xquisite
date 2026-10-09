// September 2026 release video: the two tenant-facing features that shipped —
// Booking Policy (PR #98) and Staff accounts (PR #103). Title card, guided tour
// of the seeded "Marigold" demo with burned-in captions + full-screen cards,
// end card — all composited live in the browser. Output: one silent .webm.
//
// Auth is done ONCE up front into a storageState file (owner + booking customer
// share the cookie jar), so the capture itself never sits on a login form or a
// cold dashboard render. Delete the auth file or set REAUTH=1 to refresh it.
//
// Prereqs (this session sets them up):
//   - app on http://127.0.0.1:8000, MySQL up, DemoContentSeeder run
//   - tenant 1 (slug "demo") has booking_terms + require_booking_terms_acceptance
//   - demo staff seeded (1 Manager + 2 Employees), all @marigold.co.za
//   - disposable booking customer video-demo@xquisite.local / demo-video-1234
//
// Run:  node tests/Browser/DemoCapture/release-2026-09.mjs

import { chromium } from 'playwright';
import { resolve } from 'path';
import { existsSync } from 'fs';

const BASE  = process.env.DEMO_BASE || 'http://127.0.0.1:8000';
const OUT   = process.env.CAP_OUT  || resolve('storage/app/demo-captures/release-2026-09');
const AUTH  = process.env.AUTH_FILE || resolve(OUT, 'release-auth.json');
const OWNER = { email: 'demo@xquisite.co.za',        pass: 'demo1234' };
const CUST  = { email: 'video-demo@xquisite.local',  pass: 'demo-video-1234' };

const pace = (ms) => new Promise((r) => setTimeout(r, ms));

// Overlay layer (caption pill + full-screen card + highlight ring), re-created on
// every navigation. document.documentElement can be null the instant an init
// script fires, so boot() retries until it exists instead of assuming it's there.
const OVERLAY = `
(() => {
  if (window.__ov) return;
  window.__ov = true;
  function boot() {
    var root = document.documentElement;
    if (!root) { setTimeout(boot, 0); return; }
    var mk = (id, css) => { var e = document.createElement('div'); e.id = id; e.style.cssText = css; root.appendChild(e); return e; };
    var cap = mk('__cap',
      "position:fixed;left:28px;bottom:28px;z-index:2147483646;background:rgba(8,12,20,.85);" +
      "color:#fff;font:500 18px/1.4 'Segoe UI',system-ui,sans-serif;padding:11px 17px;border-radius:8px;" +
      "border-left:3px solid #D4AF37;opacity:0;transition:opacity .45s ease;max-width:60ch;pointer-events:none");
    var card = mk('__card',
      "position:fixed;inset:0;z-index:2147483647;background:#0b1220;color:#fff;display:flex;flex-direction:column;" +
      "align-items:center;justify-content:center;text-align:center;font-family:'Segoe UI',system-ui,sans-serif;" +
      "opacity:0;transition:opacity .5s ease;pointer-events:none");
    var ring = mk('__ring',
      "position:fixed;z-index:2147483645;border:3px solid #D4AF37;border-radius:12px;" +
      "box-shadow:0 0 0 4px rgba(212,175,55,.25);opacity:0;transition:opacity .35s ease;pointer-events:none");
    window.__cap = (t) => { cap.textContent = t || ''; cap.style.opacity = t ? '1' : '0'; };
    window.__card = (h) => { card.innerHTML = h || ''; card.style.opacity = h ? '1' : '0'; };
    window.__ring = (sel) => {
      var el = sel && document.querySelector(sel);
      if (!el) { ring.style.opacity = '0'; return; }
      var r = el.getBoundingClientRect();
      ring.style.left = (r.left - 6) + 'px'; ring.style.top = (r.top - 6) + 'px';
      ring.style.width = (r.width + 12) + 'px'; ring.style.height = (r.height + 12) + 'px';
      ring.style.opacity = '1';
    };
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
async function ring(page, sel)  { await ensureOverlay(page); await page.evaluate((x) => window.__ring(x), sel).catch(() => {}); }

const SNAPDIR = process.env.SNAPDIR;
let snapN = 0;
async function snap(page, name) {
  if (!SNAPDIR) return;
  await page.screenshot({ path: `${SNAPDIR}/${String(++snapN).padStart(2, '0')}_${name}.png` }).catch(() => {});
}

const TITLE = `
  <div style="font-size:44px;font-weight:600;letter-spacing:.3px;line-height:1.15">Two new things<br>this month</div>
  <div style="width:44px;height:3px;background:#D4AF37;margin:26px 0"></div>
  <div style="font-size:20px;color:#D4AF37;letter-spacing:.5px">Booking Policy &nbsp;&middot;&nbsp; Staff accounts</div>
  <div style="font-size:15px;color:#9fb3c8;margin-top:14px">Xquisite Creations Suite</div>`;
const BRIDGE = `
  <div style="font-size:34px;font-weight:600;letter-spacing:.3px">What your client sees</div>
  <div style="width:44px;height:3px;background:#D4AF37;margin:22px auto 0"></div>`;
const ENDCARD = `
  <div style="font-size:34px;font-weight:600;letter-spacing:.3px">Both live now</div>
  <div style="font-size:18px;color:#9fb3c8;margin-top:14px">Find them in your settings</div>
  <div style="width:44px;height:3px;background:#D4AF37;margin:24px 0"></div>
  <div style="font-size:16px;color:#9fb3c8">xquisite.brightfinance-x.co.za</div>`;

async function cleanChrome(page) {
  await page.evaluate(() => {
    // Top "exploring a live demo" ribbon: find the phrase, climb to the full-width
    // top banner ancestor, hide that whole bar (not just its text node).
    for (const el of document.querySelectorAll('body *')) {
      if (!/exploring a live demo/i.test(el.textContent || '')) continue;
      let node = el;
      for (let i = 0; i < 6 && node && node.parentElement && node.parentElement !== document.body; i++) {
        const r = node.getBoundingClientRect();
        if (r.top < 10 && r.width > window.innerWidth * 0.8 && r.height < 120) break;
        node = node.parentElement;
      }
      if (node) node.style.display = 'none';
      break;
    }
    // Floating WhatsApp / chat bubble, bottom-right.
    document.querySelectorAll('a[href*="wa.me"],a[href*="whatsapp"],[class*="whatsapp"],[class*="Whatsapp"],[id*="whatsapp"]').forEach((e) => {
      const r = e.getBoundingClientRect();
      if (r.bottom > window.innerHeight - 220 && r.right > window.innerWidth - 220) {
        (e.closest('div') || e).style.display = 'none';
      }
    });
    // Leftover *.xquisite.local test-account artefacts: staff-table rows and the
    // "Booking as …" identity ribbon on the confirm page.
    document.querySelectorAll('tr, div, p, span').forEach((el) => {
      const t = el.textContent || '';
      if (/@xquisite\.local/i.test(t) && (t.length < 120 || el.tagName === 'TR')) el.style.display = 'none';
    });
  }).catch(() => {});
}

// ── One-time: log both identities into a shared storageState ─────────────
async function bootstrapAuth() {
  const b = await chromium.launch();
  const ctx = await b.newContext({ viewport: { width: 1280, height: 800 } });
  const p = await ctx.newPage();
  for (const [path, creds] of [['/login', OWNER], ['/book/demo/login', CUST]]) {
    await p.goto(BASE + path, { waitUntil: 'domcontentloaded' });
    await p.fill('input[name="email"]', creds.email);
    await p.fill('input[name="password"]', creds.pass);
    const from = p.url();
    await p.evaluate(() => {
      const f = document.querySelector('input[name="password"]').closest('form');
      f.requestSubmit ? f.requestSubmit() : f.submit();
    });
    await p.waitForURL((u) => u.toString() !== from, { timeout: 30000 }).catch(() => {});
    await p.waitForLoadState('domcontentloaded').catch(() => {});
    await pace(800);
    console.log('  authed', creds.email, '→', p.url());
  }
  await ctx.storageState({ path: AUTH });
  await b.close();
  console.log('  wrote', AUTH);
}

async function scrollTo(page, sel) {
  await page.evaluate((s) => {
    const el = document.querySelector(s);
    if (el) el.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }, sel).catch(() => {});
  await pace(1100);
}

// Navigate to a page that must be authed; hold `cover` up until `ready` shows.
async function toScene(page, path, ready, cover) {
  await page.goto(BASE + path, { waitUntil: 'domcontentloaded' }).catch(() => {});
  await ensureOverlay(page);
  if (cover) await card(page, cover);
  if (ready) await page.waitForSelector(ready, { timeout: 12000 }).catch(() => {});
  await cleanChrome(page);
  await pace(250);
}

// ── main ───────────────────────────────────────────────────────────────
if (process.env.REAUTH || !existsSync(AUTH)) {
  console.log('bootstrapping auth…');
  await bootstrapAuth();
}

const browser = await chromium.launch();
const context = await browser.newContext({
  viewport: { width: 1280, height: 800 },
  deviceScaleFactor: 2,
  storageState: AUTH,
  recordVideo: { dir: OUT, size: { width: 1280, height: 800 } },
});
const page = await context.newPage();
await page.addInitScript(OVERLAY);

// ── Title card ─────────────────────────────────────────────────────────
await page.goto(BASE + '/profile', { waitUntil: 'domcontentloaded' }).catch(() => {});
await ensureOverlay(page);
await card(page, TITLE);
await snap(page, 'title');
await page.waitForSelector('#booking-policy', { timeout: 12000 }).catch(() => {});
await cleanChrome(page);
await pace(3000);
await card(page, '');

// ── Scene 1: Booking Policy (owner settings) ───────────────────────────
await pace(500);
await caption(page, 'Booking Policy. Write your cancellation and no-show terms once.');
await scrollTo(page, '#booking-policy');
await snap(page, 'policy_settings');
await pace(2400);
await caption(page, 'They show to clients before every booking.');
await ring(page, '#booking_terms');
await pace(2400);
await ring(page, 'input[name="require_booking_terms_acceptance"]');
await caption(page, 'Turn on "require acceptance" and the client ticks to agree.');
await snap(page, 'policy_require');
await pace(2800);
await ring(page, null);
await caption(page, '');
await pace(400);

// ── Scene 2: what the client sees (customer confirm page) ──────────────
const dt = new Date(Date.now() + 4 * 864e5);
dt.setHours(10, 0, 0, 0);
const stamp = `${dt.getFullYear()}-${String(dt.getMonth() + 1).padStart(2, '0')}-${String(dt.getDate()).padStart(2, '0')}T10:00:00`;
await toScene(page, `/book/demo/confirm?service_ids%5B%5D=30&scheduled_at=${stamp}`, 'text=Terms & Cancellation Policy', BRIDGE);
await pace(1500);
await card(page, '');
await pace(500);
await caption(page, 'On the confirm screen your client sees: the policy, and a tick-to-agree box.');
await scrollTo(page, '.whitespace-pre-line, [class*="pre-line"]');
await snap(page, 'client_confirm');
await pace(2600);
await caption(page, 'The moment they agree is saved on the appointment.');
await pace(2600);
await caption(page, '');
await pace(400);

// ── Scene 3: Staff accounts — the list ───────────────────────────────
await toScene(page, '/admin/users', 'table');
await pace(400);
await caption(page, 'Staff accounts. Your team sign in as themselves, not one shared login.');
await snap(page, 'staff_list');
await pace(3000);
await page.mouse.wheel(0, 260); await pace(1200);
await caption(page, '');
await pace(400);

// ── Scene 4: Staff accounts — add a person ───────────────────────────
await toScene(page, '/admin/users/create', '#role');
await pace(400);
await caption(page, 'Add a person, set their first password, hand it over. No email invite.');
await scrollTo(page, '#role');
await ring(page, '#role');
await snap(page, 'staff_create_role');
await pace(2800);
await caption(page, 'Pick a role: Manager runs the business, Employee does the day-to-day.');
await pace(3000);
await ring(page, null);
await caption(page, '');
await pace(500);

// ── End card ────────────────────────────────────────────────────────
await card(page, ENDCARD);
await snap(page, 'endcard');
await pace(3000);

await context.close();
await browser.close();
const savedPath = await page.video()?.path().catch(() => null);
console.log('video:', savedPath || `(check ${OUT})`);

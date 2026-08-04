/**
 * Runtime creative switcher test.
 *
 * PHP renders a dual-creative slot; Chromium executes it. Asserts that
 * exactly one creative is inserted per slot, that it is the right one for
 * the viewport, and that each unit's invoke.js read its own atOptions rather
 * than a neighbour's — the failure mode that would silently serve a 320×50
 * creative into a 728×90 slot.
 *
 *   npm install playwright
 *   node design/tests/ad-switcher.js
 *
 * Set CHROMIUM_PATH to use a browser Playwright did not download itself.
 * Exits non-zero on failure.
 */
const { execFileSync } = require('child_process');
const path = require('path');
const { chromium } = require('playwright');

const FIXTURE = path.join(__dirname, 'render-ad-fixture.php');
const html = execFileSync('php', [FIXTURE], { encoding: 'utf8' });

const failures = [];
function check(label, condition) {
  if (!condition) failures.push(label);
  console.log(`  [${condition ? 'PASS' : 'FAIL'}] ${label}`);
}

async function measure(browser, width) {
  const page = await browser.newPage({ viewport: { width, height: 800 } });
  const errors = [];
  page.on('pageerror', e => errors.push(e.message));
  await page.setContent(html, { waitUntil: 'load' });
  await page.waitForTimeout(500);

  const state = await page.evaluate(() => {
    const slots = {};
    document.querySelectorAll('.hr-ad').forEach(ad => {
      const inner = ad.querySelector('.hr-ad__inner');
      slots[ad.getAttribute('data-hr-ad-slot')] = {
        scripts: inner.querySelectorAll('script').length,
        inlineText: Array.from(inner.querySelectorAll('script'))
          .map(s => s.textContent).join(' '),
        switching: inner.hasAttribute('data-hr-ad-desktop')
      };
    });
    return { slots, seen: window.__seen || [] };
  });

  await page.close();
  return { state, errors };
}

(async () => {
  const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || undefined
  });

  console.log('Mobile viewport (390px)');
  const mobile = await measure(browser, 390);
  const mKeys = mobile.state.seen.map(s => s.key);

  check('header inserted the 320×50 creative', mKeys.includes('mobile-320'));
  check('header did NOT insert the 728×90 creative', !mKeys.includes('desktop-728'));
  check('feed inserted its mobile creative', mKeys.includes('mobile-feed'));
  check('feed did NOT insert its desktop creative', !mKeys.includes('desktop-feed'));
  check('inline slot ran untouched', mKeys.includes('inline-300'));
  check('exactly three units executed in total', mobile.state.seen.length === 3);

  const mHeader = mobile.state.seen.find(s => s.key === 'mobile-320');
  check('header unit read its own width (320)', mHeader && mHeader.w === 320);
  check('header unit read its own height (50)', mHeader && mHeader.h === 50);

  const mFeed = mobile.state.seen.find(s => s.key === 'mobile-feed');
  check('feed unit read its own width (300) — no atOptions race', mFeed && mFeed.w === 300);

  check('no JavaScript errors', mobile.errors.length === 0);

  console.log('\nDesktop viewport (1280px)');
  const desktop = await measure(browser, 1280);
  const dKeys = desktop.state.seen.map(s => s.key);

  check('header inserted the 728×90 creative', dKeys.includes('desktop-728'));
  check('header did NOT insert the 320×50 creative', !dKeys.includes('mobile-320'));
  check('feed inserted its desktop creative', dKeys.includes('desktop-feed'));
  check('exactly three units executed in total', desktop.state.seen.length === 3);

  const dHeader = desktop.state.seen.find(s => s.key === 'desktop-728');
  check('header unit read its own width (728)', dHeader && dHeader.w === 728);

  const dFeed = desktop.state.seen.find(s => s.key === 'desktop-feed');
  check('feed unit read its own config — no atOptions race', dFeed && dFeed.w === 728 && dFeed.h === 90);

  check('no JavaScript errors', desktop.errors.length === 0);

  console.log('\nMarkup contract');
  check('dual-creative slot starts empty in the HTML',
    desktop.state.slots.header.switching === true);
  check('single-creative slot carries no switching attributes',
    desktop.state.slots.after_intro.switching === false);

  await browser.close();

  console.log('');
  if (failures.length) {
    console.log(`${failures.length} FAILED:\n  - ${failures.join('\n  - ')}`);
    process.exit(1);
  }
  console.log('ALL CHECKS PASSED');
})();

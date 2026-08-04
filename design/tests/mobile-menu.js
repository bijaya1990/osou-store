/**
 * Mobile drawer regression test.
 *
 * Loads the real header markup with the real stylesheet and the real
 * navigation script, opens the menu at phone widths, and asserts the drawer
 * fills the viewport instead of being squeezed into the 48px icon column —
 * which is what happened when the nav was left to auto-place inside the
 * header's three-column grid.
 *
 * Also covers Escape-to-close, focus return, menu/search exclusivity, and
 * the 48px touch-target floor.
 *
 *   npm install playwright
 *   node design/tests/mobile-menu.js
 *
 * Set CHROMIUM_PATH to use a browser Playwright did not download itself.
 * Exits non-zero on failure.
 */
const fs = require('fs');
const path = require('path');
const { chromium } = require('playwright');

const ROOT = path.resolve(__dirname, '..', '..');
const THEME = path.join(ROOT, 'hauntedreal-child');
const SCREENS = path.join(ROOT, 'design', 'screens');

const doc = `<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>${fs.readFileSync(`${THEME}/assets/css/main.css`, 'utf8')}
${fs.readFileSync(`${SCREENS}/_photos.css`, 'utf8')}</style></head>
<body class="hr-body">
${fs.readFileSync(`${SCREENS}/_header.html`, 'utf8')}
${fs.readFileSync(`${SCREENS}/home.html`, 'utf8')}
${fs.readFileSync(`${SCREENS}/_footer.html`, 'utf8')}
<script>${fs.readFileSync(`${THEME}/assets/js/navigation.js`, 'utf8')}<\/script>
</body></html>`;

async function probe(page, width) {
  await page.setViewportSize({ width, height: 800 });
  await page.waitForTimeout(200);
  return page.evaluate(() => {
    const nav = document.querySelector('.hr-nav');
    const link = nav.querySelector('a');
    const toggle = document.querySelector('.hr-navtoggle');
    return {
      navW: Math.round(nav.getBoundingClientRect().width),
      linkW: Math.round(link.getBoundingClientRect().width),
      linkH: Math.round(link.getBoundingClientRect().height),
      visible: getComputedStyle(nav).display !== 'none',
      expanded: toggle.getAttribute('aria-expanded'),
      docScrollW: document.documentElement.scrollWidth
    };
  });
}

(async () => {
  const browser = await chromium.launch({
    executablePath: process.env.CHROMIUM_PATH || undefined
  });
  const page = await browser.newPage({ viewport: { width: 390, height: 800 } });
  const errs = [];
  page.on('pageerror', e => errs.push('PAGEERROR: ' + e.message));
  await page.setContent(doc, { waitUntil: 'load' });

  const results = {};

  // --- 390px: closed, then open ---
  results.mobileClosed = await probe(page, 390);
  await page.click('.hr-navtoggle');
  await page.waitForTimeout(300);
  results.mobileOpen = await probe(page, 390);
  await page.screenshot({ path: 'menu-390-open.png' });

  // Escape must close it and return focus to the toggle.
  await page.keyboard.press('Escape');
  await page.waitForTimeout(200);
  results.afterEscape = await probe(page, 390);
  results.focusReturned = await page.evaluate(
    () => document.activeElement.classList.contains('hr-navtoggle')
  );

  // Search panel should open and close the menu.
  await page.click('.hr-navtoggle');
  await page.waitForTimeout(150);
  await page.click('.hr-searchtoggle');
  await page.waitForTimeout(250);
  results.searchOpensMenuCloses = await page.evaluate(() => ({
    search: document.querySelector('.hr-searchpanel').classList.contains('is-open'),
    menu: document.querySelector('.hr-nav').classList.contains('is-open')
  }));
  await page.screenshot({ path: 'menu-390-search.png' });

  // --- 360px, the narrowest common phone ---
  await page.keyboard.press('Escape');
  await page.setViewportSize({ width: 360, height: 800 });
  await page.click('.hr-navtoggle');
  await page.waitForTimeout(250);
  results.narrowOpen = await probe(page, 360);

  // --- 768px tablet: still a drawer ---
  await page.setViewportSize({ width: 768, height: 900 });
  await page.waitForTimeout(250);
  results.tabletOpen = await probe(page, 768);
  await page.screenshot({ path: 'menu-768-open.png' });

  // --- 1280px: permanent bar, drawer state irrelevant ---
  results.desktop = await probe(page, 1280);
  await page.screenshot({ path: 'menu-1280.png' });

  console.log(JSON.stringify(results, null, 1));

  const fails = [];
  if (results.mobileClosed.visible) fails.push('drawer visible before opening');
  if (!results.mobileOpen.visible) fails.push('drawer did not open');
  if (results.mobileOpen.navW !== 390) fails.push(`drawer width ${results.mobileOpen.navW}, expected 390`);
  if (results.mobileOpen.linkW !== 390) fails.push(`link width ${results.mobileOpen.linkW}, expected 390`);
  if (results.mobileOpen.linkH < 48) fails.push(`link height ${results.mobileOpen.linkH} below 48px touch target`);
  if (results.afterEscape.visible) fails.push('Escape did not close the drawer');
  if (!results.focusReturned) fails.push('focus did not return to the toggle');
  if (!results.searchOpensMenuCloses.search || results.searchOpensMenuCloses.menu) fails.push('search/menu are not mutually exclusive');
  if (results.narrowOpen.navW !== 360) fails.push(`360px drawer width ${results.narrowOpen.navW}`);
  if (results.tabletOpen.navW !== 768) fails.push(`768px drawer width ${results.tabletOpen.navW}`);
  if (!results.desktop.visible) fails.push('desktop nav not visible');
  if (results.desktop.docScrollW > 1280) fails.push('horizontal overflow at 1280');

  console.log(errs.length ? errs.join('\n') : 'NO JS ERRORS');
  console.log(fails.length ? 'FAIL:\n  ' + fails.join('\n  ') : 'ALL CHECKS PASSED');
  await browser.close();
  process.exit(fails.length ? 1 : 0);
})();

#!/usr/bin/env python3
"""
Build the HauntedReal design preview.

Reads the theme's real stylesheet and the screen fragments in screens/, and
emits a single self-contained page that renders every screen inside a
right-sized iframe. Because the CSS comes straight out of the theme, the
mockups cannot drift away from the code — rebuild after any CSS change.

    python3 design/build-preview.py

Outputs:
    design/preview.html           standalone document, open it in a browser
    design/preview.artifact.html  same page as a fragment, for publishing
"""

import json
import pathlib

ROOT = pathlib.Path(__file__).resolve().parent
THEME = ROOT.parent / "hauntedreal-child"
SCREENS = ROOT / "screens"


def read(path: pathlib.Path) -> str:
    return path.read_text(encoding="utf-8")


def js(value) -> str:
    """Serialize to a JS literal that is safe inside a <script> block."""
    return json.dumps(value).replace("</script", "<\\/script")


# --------------------------------------------------------------------------
# Screen inventory. `widths` lists the viewports offered for each screen; the
# first is the one it opens at.
# --------------------------------------------------------------------------

SCREEN_LIST = [
    {
        "id": "home",
        "file": "home.html",
        "title": "Homepage",
        "template": "front-page.php",
        "note": "One lead story, a secondary column, then the investigation grid, the "
                "Haunted America strip and the community feed. Desktop and mobile are the "
                "same markup at different viewports — switch the width to see the whole "
                "responsive system, including the leaderboard dropping from 728×90 to 320×50.",
        "widths": [1280, 390, 768, 1512],
    },
    {
        "id": "single",
        "file": "single.html",
        "title": "Single Article",
        "template": "single.php",
        "note": "The most important reading surface on the site. One H1, a ~72-character "
                "measure that holds at any width, and ad slots 02 and 03 injected between "
                "paragraphs by the theme rather than pasted into the post. The sidebar and "
                "its 300×250 exist only from 1024px up.",
        "widths": [1280, 390, 768, 1512],
    },
    {
        "id": "category",
        "file": "category.html",
        "title": "Category Archive",
        "template": "category.php",
        "note": "Sibling sections become a chip row that scrolls horizontally on its own — "
                "the page body never scrolls sideways.",
        "widths": [1280, 390, 768],
    },
    {
        "id": "haunted-america",
        "file": "haunted-america.html",
        "title": "Haunted America — US States",
        "template": "page-templates/haunted-america.php",
        "note": "The section index. States we have stories from are separated from the ones "
                "still waiting for their first account, which turns an empty state into a "
                "call for submissions.",
        "widths": [1280, 390, 768],
    },
    {
        "id": "search",
        "file": "search.html",
        "title": "Search Results",
        "template": "search.php",
        "note": "Results span editorial reporting, community accounts and pages. The type "
                "label above each result is doing real work: it is how a reader knows which "
                "of the three they are about to open.",
        "widths": [1280, 390, 768],
    },
    {
        "id": "author",
        "file": "author.html",
        "title": "Author Profile",
        "template": "author.php",
        "note": "A masthead credit page. Title, beat and years active are editable per user "
                "in the WordPress profile screen.",
        "widths": [1280, 390, 768],
    },
    {
        "id": "ghost-archive",
        "file": "ghost-archive.html",
        "title": "Ghost Experience Archive",
        "template": "archive-ghost_story.php",
        "note": "The community feed. Every card carries the cold spectral accent and a "
                "permanent COMMUNITY EXPERIENCE badge, and leads with the witness's own "
                "words rather than an editor's summary.",
        "widths": [1280, 390, 768],
    },
    {
        "id": "ghost-single",
        "file": "ghost-single.html",
        "title": "Single Ghost Experience",
        "template": "single-ghost_story.php",
        "note": "Witness, location, date and experience type sit above the account in a "
                "detail strip; a standing disclaimer sits below it. No staff byline appears "
                "anywhere on this template.",
        "widths": [1280, 390, 768],
    },
    {
        "id": "submit",
        "file": "submit.html",
        "title": "Submit Ghost Story",
        "template": "page-templates/submit-ghost-story.php",
        "note": "Three short fieldsets instead of one long form. Success and error states "
                "are shown together at the bottom of this mockup for review — only one "
                "appears at a time in the build.",
        "widths": [768, 390, 1280],
    },
    {
        "id": "page",
        "file": "page.html",
        "title": "Standard Page",
        "template": "page.php",
        "note": "Narrow measure, no sidebar, no advertising. The pages that make a "
                "publication look like one.",
        "widths": [768, 390, 1280],
    },
    {
        "id": "404",
        "file": "404.html",
        "title": "404",
        "template": "404.php",
        "note": "A dead end gets a search field and three real stories. No ad slots — there "
                "is nothing here to fund.",
        "widths": [1280, 390, 768],
    },
]


PALETTE = [
    ("Void", "#08090c", "Page ground"),
    ("Surface", "#0e1015", "Cards, masthead"),
    ("Raised", "#141822", "Hover, panels"),
    ("Text", "#ecebe8", "Body copy — 16.4:1"),
    ("Muted", "#a8adb8", "Meta, captions — 8.1:1"),
    ("Ember", "#e5903a", "Editorial accent"),
    ("Spectral", "#8fb9d4", "Community accent"),
    ("Verified", "#2f6b4c", "Status only"),
]

TYPE_SCALE = [
    ("Hero", "2rem → 3.75rem", "clamp() — homepage lead", "display"),
    ("H1", "1.75rem → 3rem", "Article titles", "display"),
    ("H2", "1.375rem → 1.875rem", "Section heads", "display"),
    ("H3", "1.1875rem → 1.4375rem", "Card titles", "display"),
    ("Lead", "1.0625rem → 1.1875rem", "Article body, standfirst", "body"),
    ("Base", "1rem", "UI, excerpts", "body"),
    ("Meta", "0.8125rem", "Byline, location, date", "body"),
    ("Label", "0.75rem", "Kickers, badges — +0.13em tracking", "body"),
]

AD_SLOTS = [
    ("01", "Header / Leaderboard", "728×90 · 320×50", "90px / 50px",
     "hauntedreal_header_ad", "Below the masthead"),
    ("02", "Article Introduction", "Responsive", "250px",
     "hauntedreal_after_intro_ad", "After paragraph 2"),
    ("03", "Mid Article", "300×250 or native", "280px / 250px",
     "hauntedreal_mid_content_ad", "~45% through the body"),
    ("04", "Article Bottom", "Responsive", "250px",
     "hauntedreal_after_content_ad", "Before related stories"),
    ("05", "Desktop Sidebar", "300×250", "250px",
     "hauntedreal_sidebar_ad", "1024px and up only"),
    ("06", "Homepage Feed", "Native", "250px",
     "hauntedreal_home_feed_ad", "Between article groups"),
    ("07", "Social Bar", "Overlay", "none — floats",
     "hauntedreal_social_bar_ad", "Site-wide, printed in the footer"),
]

BREAKPOINTS = [
    ("Mobile", "320–767px", "Single column · hamburger drawer · 56px header · no sidebar"),
    ("Tablet", "768–1023px", "Two-up cards · 728×90 leaderboard · still no sidebar"),
    ("Desktop", "1024–1439px", "Masthead bar · 3–4 column grids · sidebar and slot 05 appear"),
    ("Large", "1440px+", "Wider measure (46rem) · 336px sidebar · larger hero"),
]


def build_body() -> str:
    css = read(THEME / "assets/css/main.css") + "\n" + read(SCREENS / "_photos.css")
    # The theme's real navigation script runs inside every frame, so the menu
    # and search disclosures behave here exactly as they do on the site. A
    # preview where the drawer cannot open is a preview that cannot catch a
    # bug in the drawer.
    theme_js = read(THEME / "assets/js/navigation.js")
    header = read(SCREENS / "_header.html")
    footer = read(SCREENS / "_footer.html")

    frags = {}
    screens = []

    for screen in SCREEN_LIST:
        frags[screen["id"]] = header + read(SCREENS / screen["file"]) + footer
        screens.append({k: v for k, v in screen.items() if k != "file"})

    swatches = "\n".join(
        f'<li class="swatch"><span class="swatch__chip" style="background:{hexv}"></span>'
        f'<span class="swatch__name">{name}</span>'
        f'<code class="swatch__hex">{hexv}</code>'
        f'<span class="swatch__use">{use}</span></li>'
        for name, hexv, use in PALETTE
    )

    type_rows = "\n".join(
        f'<tr><th scope="row">{name}</th><td><code>{size}</code></td>'
        f'<td>{use}</td><td class="dim">{face}</td></tr>'
        for name, size, use, face in TYPE_SCALE
    )

    ad_rows = "\n".join(
        f'<tr><td class="slotnum">{num}</td><th scope="row">{name}</th>'
        f'<td>{fmt}</td><td class="num">{reserved}</td>'
        f'<td><code>{hook}</code></td><td class="dim">{pos}</td></tr>'
        for num, name, fmt, reserved, hook, pos in AD_SLOTS
    )

    bp_rows = "\n".join(
        f'<tr><th scope="row">{name}</th><td class="num">{rng}</td><td>{desc}</td></tr>'
        for name, rng, desc in BREAKPOINTS
    )

    nav_links = "\n".join(
        f'<li><a href="#screen-{s["id"]}">{s["title"]}</a></li>' for s in SCREEN_LIST
    )

    return f"""<title>HauntedReal — Design System &amp; Screens</title>
<style>{GALLERY_CSS}</style>

<header class="masthead">
  <p class="eyebrow">GeneratePress Free child theme · no page builder</p>
  <h1>HauntedReal</h1>
  <p class="lede">
    A dark, cinematic design system for a serious American paranormal publication —
    and the thirteen screens it produces. Every preview below is rendered with the
    theme's own <code>assets/css/main.css</code>, at a real viewport width, so what
    you see is what the build outputs rather than a picture of it.
  </p>
  <ul class="facts">
    <li><b>1</b><span>stylesheet</span></li>
    <li><b>~1KB</b><span>of JavaScript</span></li>
    <li><b>7</b><span>ad slots, hooked not hard-coded</span></li>
    <li><b>4</b><span>breakpoints, mobile first</span></li>
  </ul>
</header>

<nav class="jump" aria-label="Screens">
  <span class="jump__label">Screens</span>
  <ul>{nav_links}</ul>
</nav>

<section class="spec" aria-labelledby="spec-color">
  <h2 id="spec-color">Palette</h2>
  <p class="spec__note">
    Two accents carry the whole system. <b>Ember</b> is HauntedReal reporting —
    lantern light, not blood. <b>Spectral</b> is community-submitted experience —
    cold, other, never confused with editorial. Body text sits at 16.4:1 against the
    ground; the muted tone still clears 8:1. Nothing on this site is dark grey on black.
  </p>
  <ul class="swatches">{swatches}</ul>
</section>

<section class="spec" aria-labelledby="spec-type">
  <h2 id="spec-type">Type scale</h2>
  <p class="spec__note">
    A serif display face for credibility, a system sans for body speed — no webfont
    request, no render-blocking CDN, no invisible-text flash. Sizes are
    <code>clamp()</code> values, so type scales continuously instead of jumping at
    breakpoints. Swap <code>--hr-font-display</code> in one place to introduce a
    licensed face later.
  </p>
  <div class="tablewrap">
    <table>
      <thead><tr><th>Role</th><th>Size</th><th>Used for</th><th>Face</th></tr></thead>
      <tbody>{type_rows}</tbody>
    </table>
  </div>
</section>

<section class="spec" aria-labelledby="spec-ads">
  <h2 id="spec-ads">Advertising slots</h2>
  <p class="spec__note">
    No network code is ever written into a template. Each slot fires a named action
    and resolves its markup in <code>inc/ads.php</code>, so Adsterra can be replaced
    from the Customizer without touching a template file. Every in-page slot reserves its
    height <em>before</em> anything loads — the reserved column below is the CLS budget.
    Slot 07 is an overlay: it floats, so it reserves nothing.
  </p>
  <div class="tablewrap">
    <table>
      <thead>
        <tr><th>Slot</th><th>Position</th><th>Format</th><th>Reserved</th><th>Hook</th><th>Placement</th></tr>
      </thead>
      <tbody>{ad_rows}</tbody>
    </table>
  </div>
</section>

<section class="spec" aria-labelledby="spec-bp">
  <h2 id="spec-bp">Breakpoints</h2>
  <p class="spec__note">
    Mobile is the base layer, not a shrunken desktop: the small-screen rules are what
    the stylesheet opens with, and each breakpoint only adds. The sidebar advertisement
    is never forced onto a phone — it is not in the markup below 1024px.
  </p>
  <div class="tablewrap">
    <table>
      <thead><tr><th>Range</th><th>Width</th><th>What changes</th></tr></thead>
      <tbody>{bp_rows}</tbody>
    </table>
  </div>
</section>

<section class="screens" aria-label="Screen designs">
  <h2 class="screens__title">The screens</h2>
  <div id="screen-mount"></div>
</section>

<footer class="colophon">
  <p>
    Built as a GeneratePress Free child theme: PHP, HTML5, CSS3 and one small vanilla
    script. No Elementor, Divi, WPBakery or GeneratePress Premium. Rebuild this page
    with <code>python3 design/build-preview.py</code> after any stylesheet change.
  </p>
</footer>

<script>
const THEME_CSS = {js(css)};
const THEME_JS = {js(theme_js)};
const FRAGMENTS = {js(frags)};
const SCREENS = {js(screens)};
{GALLERY_JS}
</script>
"""


GALLERY_CSS = """
:root {
  color-scheme: light dark;
  --g-ground:  #f4f2ef;
  --g-surface: #ffffff;
  --g-sunken:  #ebe8e3;
  --g-ink:     #17151a;
  --g-muted:   #6b6570;
  --g-line:    #ddd8d1;
  --g-accent:  #a8560f;
  --g-spectral:#2c5670;
  --g-shadow:  0 1px 2px rgba(30,20,10,.06), 0 12px 32px -18px rgba(30,20,10,.35);
  --g-display: "Iowan Old Style","Palatino Linotype",Palatino,Georgia,"Times New Roman",serif;
  --g-body: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
  --g-mono: ui-monospace,SFMono-Regular,"SF Mono",Menlo,Consolas,monospace;
  --g-max: 1180px;
}

@media (prefers-color-scheme: dark) {
  :root {
    --g-ground:  #0b0c10;
    --g-surface: #131519;
    --g-sunken:  #08090c;
    --g-ink:     #eceae7;
    --g-muted:   #9a99a4;
    --g-line:    #262932;
    --g-accent:  #e5903a;
    --g-spectral:#8fb9d4;
    --g-shadow:  0 1px 2px rgba(0,0,0,.5), 0 16px 40px -20px rgba(0,0,0,.9);
  }
}

:root[data-theme="dark"] {
  --g-ground:  #0b0c10;
  --g-surface: #131519;
  --g-sunken:  #08090c;
  --g-ink:     #eceae7;
  --g-muted:   #9a99a4;
  --g-line:    #262932;
  --g-accent:  #e5903a;
  --g-spectral:#8fb9d4;
  --g-shadow:  0 1px 2px rgba(0,0,0,.5), 0 16px 40px -20px rgba(0,0,0,.9);
}

:root[data-theme="light"] {
  --g-ground:  #f4f2ef;
  --g-surface: #ffffff;
  --g-sunken:  #ebe8e3;
  --g-ink:     #17151a;
  --g-muted:   #6b6570;
  --g-line:    #ddd8d1;
  --g-accent:  #a8560f;
  --g-spectral:#2c5670;
  --g-shadow:  0 1px 2px rgba(30,20,10,.06), 0 12px 32px -18px rgba(30,20,10,.35);
}

* { box-sizing: border-box; }

body {
  margin: 0;
  padding: 0 clamp(1rem, 4vw, 3rem) 5rem;
  background: var(--g-ground);
  color: var(--g-ink);
  font-family: var(--g-body);
  font-size: 16px;
  line-height: 1.6;
  -webkit-font-smoothing: antialiased;
}

h1, h2, h3 { font-family: var(--g-display); font-weight: 600; line-height: 1.15; text-wrap: balance; margin: 0; }
p { margin: 0; }
code { font-family: var(--g-mono); font-size: 0.875em; }
a { color: var(--g-accent); }
:focus-visible { outline: 2px solid var(--g-accent); outline-offset: 3px; border-radius: 2px; }

.masthead {
  max-width: var(--g-max);
  margin: 0 auto;
  padding: clamp(3rem, 8vw, 6rem) 0 clamp(2rem, 4vw, 3rem);
  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  border-bottom: 1px solid var(--g-line);
}
.eyebrow {
  font-size: 0.75rem; font-weight: 700; letter-spacing: .14em;
  text-transform: uppercase; color: var(--g-accent);
}
.masthead h1 { font-size: clamp(2.75rem, 8vw, 5rem); letter-spacing: -0.03em; }
.lede { max-width: 62ch; font-size: clamp(1.0625rem, 0.98rem + 0.4vw, 1.1875rem); color: var(--g-muted); }
.lede code { color: var(--g-ink); }

.facts { display: flex; flex-wrap: wrap; gap: 2.5rem; list-style: none; margin: .75rem 0 0; padding: 0; }
.facts li { display: flex; flex-direction: column; gap: .15rem; }
.facts b { font-family: var(--g-display); font-size: 1.875rem; line-height: 1; font-variant-numeric: tabular-nums; }
.facts span { font-size: 0.75rem; letter-spacing: .1em; text-transform: uppercase; color: var(--g-muted); }

.jump {
  position: sticky; top: 0; z-index: 20;
  max-width: var(--g-max); margin: 0 auto;
  display: flex; align-items: center; gap: 1rem;
  padding: .75rem 0;
  background: color-mix(in srgb, var(--g-ground) 92%, transparent);
  backdrop-filter: blur(8px);
  border-bottom: 1px solid var(--g-line);
  overflow-x: auto; scrollbar-width: thin;
}
.jump__label {
  flex: none; font-size: 0.6875rem; font-weight: 700;
  letter-spacing: .14em; text-transform: uppercase; color: var(--g-muted);
}
.jump ul { display: flex; gap: .35rem; list-style: none; margin: 0; padding: 0; }
.jump a {
  display: block; padding: .35rem .7rem; white-space: nowrap;
  font-size: 0.8125rem; color: var(--g-muted); text-decoration: none;
  border: 1px solid transparent; border-radius: 100px;
}
.jump a:hover { color: var(--g-ink); border-color: var(--g-line); background: var(--g-surface); }

.spec { max-width: var(--g-max); margin: 0 auto; padding: 3.5rem 0 0; display: flex; flex-direction: column; gap: 1rem; }
.spec h2 { font-size: clamp(1.5rem, 3vw, 2rem); letter-spacing: -0.015em; }
.spec__note { max-width: 68ch; color: var(--g-muted); }
.spec__note b { color: var(--g-ink); font-weight: 600; }

.swatches { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: .75rem; list-style: none; margin: .5rem 0 0; padding: 0; }
.swatch {
  display: grid; grid-template-columns: 34px 1fr; grid-template-rows: auto auto;
  gap: .15rem .75rem; align-items: center;
  padding: .75rem; background: var(--g-surface);
  border: 1px solid var(--g-line); border-radius: 4px;
}
.swatch__chip { grid-row: 1 / 3; width: 34px; height: 34px; border-radius: 3px; border: 1px solid rgba(128,128,128,.35); }
.swatch__name { font-weight: 600; font-size: 0.875rem; }
.swatch__hex { color: var(--g-muted); font-size: 0.75rem; }
.swatch__use { grid-column: 2; font-size: 0.75rem; color: var(--g-muted); }

.tablewrap { overflow-x: auto; border: 1px solid var(--g-line); border-radius: 4px; background: var(--g-surface); }
table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
th, td { padding: .7rem .9rem; text-align: left; border-bottom: 1px solid var(--g-line); vertical-align: top; }
thead th {
  font-size: 0.6875rem; letter-spacing: .1em; text-transform: uppercase;
  color: var(--g-muted); font-weight: 700; white-space: nowrap;
}
tbody tr:last-child th, tbody tr:last-child td { border-bottom: 0; }
tbody th { font-weight: 600; }
.num { font-variant-numeric: tabular-nums; white-space: nowrap; }
.dim { color: var(--g-muted); }
.slotnum {
  font-family: var(--g-display); font-size: 1rem;
  color: var(--g-accent); font-variant-numeric: tabular-nums; width: 1%;
}

.screens { max-width: var(--g-max); margin: 0 auto; padding-top: 4.5rem; }
.screens__title { font-size: clamp(1.5rem, 3vw, 2rem); letter-spacing: -0.015em; margin-bottom: 1.5rem; }

.screen { padding-top: 3rem; scroll-margin-top: 4rem; }
.screen__head { display: flex; flex-wrap: wrap; align-items: baseline; gap: .5rem 1rem; }
.screen__title { font-size: 1.375rem; letter-spacing: -0.01em; }
.screen__template {
  font-family: var(--g-mono); font-size: 0.75rem; color: var(--g-muted);
  padding: .2rem .5rem; background: var(--g-sunken);
  border: 1px solid var(--g-line); border-radius: 3px;
}
.screen__note { max-width: 72ch; margin-top: .6rem; color: var(--g-muted); font-size: 0.9375rem; }

.screen__controls { display: flex; flex-wrap: wrap; gap: .4rem; margin: 1rem 0 .6rem; }
.screen__controls button {
  font: inherit; font-size: 0.8125rem; line-height: 1;
  padding: .5rem .8rem; min-height: 36px;
  color: var(--g-muted); background: var(--g-surface);
  border: 1px solid var(--g-line); border-radius: 100px; cursor: pointer;
}
.screen__controls button:hover { color: var(--g-ink); border-color: var(--g-muted); }
.screen__controls button[aria-pressed="true"] {
  color: var(--g-ground); background: var(--g-ink); border-color: var(--g-ink); font-weight: 600;
}
.screen__controls .spacer { flex: 1 1 1rem; }

.stage {
  position: relative;
  overflow: hidden;
  background: var(--g-sunken);
  border: 1px solid var(--g-line);
  border-radius: 6px;
  box-shadow: var(--g-shadow);
}
.stage iframe { display: block; border: 0; background: #08090c; }
.stage__meta {
  display: flex; justify-content: space-between; gap: 1rem;
  margin-top: .5rem; font-size: 0.75rem; color: var(--g-muted);
  font-variant-numeric: tabular-nums;
}

.colophon {
  max-width: var(--g-max); margin: 5rem auto 0; padding-top: 2rem;
  border-top: 1px solid var(--g-line);
  font-size: 0.875rem; color: var(--g-muted);
}
.colophon p { max-width: 72ch; }

@media (prefers-reduced-motion: reduce) {
  * { transition-duration: .001ms !important; animation-duration: .001ms !important; }
}
"""


GALLERY_JS = """
(function () {
  'use strict';

  // Device viewport heights. Frames scroll internally at these heights; the
  // "Full page" control swaps to the document's own height instead.
  var VIEWPORT_HEIGHT = { 390: 780, 768: 900, 1280: 850, 1512: 900 };
  var LABEL = { 390: 'Mobile 390', 768: 'Tablet 768', 1280: 'Desktop 1280', 1512: 'Large 1512' };

  var mount = document.getElementById('screen-mount');

  SCREENS.forEach(function (screen) {
    var section = document.createElement('section');
    section.className = 'screen';
    section.id = 'screen-' + screen.id;

    var head = document.createElement('div');
    head.className = 'screen__head';
    head.innerHTML =
      '<h3 class="screen__title"></h3><span class="screen__template"></span>';
    head.querySelector('.screen__title').textContent = screen.title;
    head.querySelector('.screen__template').textContent = screen.template;
    section.appendChild(head);

    var note = document.createElement('p');
    note.className = 'screen__note';
    note.textContent = screen.note;
    section.appendChild(note);

    var controls = document.createElement('div');
    controls.className = 'screen__controls';
    section.appendChild(controls);

    var stage = document.createElement('div');
    stage.className = 'stage';
    var frame = document.createElement('iframe');
    frame.title = screen.title + ' preview';
    frame.setAttribute('loading', 'lazy');
    stage.appendChild(frame);
    section.appendChild(stage);

    var meta = document.createElement('div');
    meta.className = 'stage__meta';
    meta.innerHTML = '<span class="meta-size"></span><span class="meta-scale"></span>';
    section.appendChild(meta);

    mount.appendChild(section);

    var state = { width: screen.widths[0], full: false, rendered: false };

    function paint() {
      var doc = frame.contentDocument;
      doc.open();
      doc.write(
        '<!doctype html><html lang="en"><head><meta charset="utf-8">' +
        '<meta name="viewport" content="width=device-width,initial-scale=1">' +
        '<style>' + THEME_CSS + '</style></head><body class="hr-body">' +
        FRAGMENTS[screen.id] +
        '<script>' + THEME_JS + '<\\/script>' +
        '</body></html>'
      );
      doc.close();
      state.rendered = true;
    }

    function layout() {
      var doc = frame.contentDocument;
      if (!doc) { return; }

      var contentHeight = Math.max(
        doc.documentElement.scrollHeight,
        doc.body ? doc.body.scrollHeight : 0
      );
      var height = state.full ? contentHeight : (VIEWPORT_HEIGHT[state.width] || 850);
      var available = stage.clientWidth || section.clientWidth;
      var scale = Math.min(1, available / state.width);

      frame.style.width = state.width + 'px';
      frame.style.height = height + 'px';
      frame.style.transformOrigin = 'top left';
      frame.style.transform = 'scale(' + scale + ')';
      // transform-origin pins the scaled frame to the left edge, so center it
      // by hand — a 390px phone floating in a 1180px stage looks like a bug.
      frame.style.marginLeft = Math.max(0, (available - state.width * scale) / 2) + 'px';
      stage.style.height = Math.round(height * scale) + 'px';

      meta.querySelector('.meta-size').textContent =
        state.width + ' × ' + (state.full ? contentHeight + ' (full page)' : height) + ' px';
      meta.querySelector('.meta-scale').textContent =
        scale < 1 ? 'shown at ' + Math.round(scale * 100) + '%' : 'shown at 100%';
    }

    function refresh() {
      if (!state.rendered) { paint(); }
      // Two frames: one for the write to settle, one for layout to resolve.
      requestAnimationFrame(function () { requestAnimationFrame(layout); });
    }

    screen.widths.forEach(function (width) {
      var button = document.createElement('button');
      button.type = 'button';
      button.textContent = LABEL[width] || width + 'px';
      button.setAttribute('aria-pressed', width === state.width ? 'true' : 'false');
      button.addEventListener('click', function () {
        state.width = width;
        controls.querySelectorAll('button[data-width]').forEach(function (other) {
          other.setAttribute('aria-pressed', String(Number(other.dataset.width) === width));
        });
        refresh();
      });
      button.dataset.width = String(width);
      controls.appendChild(button);
    });

    var spacer = document.createElement('span');
    spacer.className = 'spacer';
    controls.appendChild(spacer);

    var fullBtn = document.createElement('button');
    fullBtn.type = 'button';
    fullBtn.textContent = 'Full page';
    fullBtn.setAttribute('aria-pressed', 'false');
    fullBtn.addEventListener('click', function () {
      state.full = !state.full;
      fullBtn.setAttribute('aria-pressed', String(state.full));
      refresh();
    });
    controls.appendChild(fullBtn);

    // Render when the section first comes near the viewport — thirteen full
    // documents at once is a lot to ask of a phone.
    if ('IntersectionObserver' in window) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            refresh();
            observer.disconnect();
          }
        });
      }, { rootMargin: '600px' });
      observer.observe(section);
    } else {
      refresh();
    }

    window.addEventListener('resize', function () {
      if (state.rendered) { layout(); }
    });
  });
}());
"""


def main() -> None:
    page = build_body()

    # The publishable fragment: no document shell, since the Artifact host
    # supplies <!doctype>, <head> and <body> at publish time.
    (ROOT / "preview.artifact.html").write_text(page, encoding="utf-8")

    # The same page wrapped in a shell, so the file opens straight from disk.
    head, _, rest = page.partition("</style>")
    standalone = (
        '<!doctype html>\n<html lang="en">\n<head>\n'
        '<meta charset="utf-8">\n'
        '<meta name="viewport" content="width=device-width,initial-scale=1">\n'
        + head
        + "</style>\n</head>\n<body>\n"
        + rest
        + "\n</body>\n</html>\n"
    )
    (ROOT / "preview.html").write_text(standalone, encoding="utf-8")

    print("Wrote design/preview.html and design/preview.artifact.html")


if __name__ == "__main__":
    main()

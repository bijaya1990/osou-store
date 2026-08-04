# HauntedReal

A dark, cinematic **GeneratePress Free child theme** for HauntedReal.com — a serious
American paranormal publication that happens to have a horror identity.

No page builder. No GeneratePress Premium. No Elementor, Divi or WPBakery. One
stylesheet, roughly a kilobyte of JavaScript, and standard WordPress template
architecture.

---

## What you need

| Requirement | Version |
|---|---|
| WordPress | 6.0 or newer (6.3+ recommended — script `defer` uses the 6.3 API and degrades gracefully below it) |
| PHP | 7.4 or newer |
| Parent theme | **GeneratePress** (the free version from wordpress.org) |

## Install

1. Install and activate **GeneratePress** (free) once — from
   *Appearance → Themes → Add New*, search "GeneratePress".
2. Upload `hauntedreal-child.zip` at *Appearance → Themes → Add New → Upload Theme*.
3. Activate **HauntedReal**.

Activating the child theme automatically:

* registers the Ghost Story post type and the US State / Experience Type taxonomies,
* seeds all 50 states plus DC into Haunted America,
* seeds a starting vocabulary of experience types,
* flushes rewrite rules so the new URLs work immediately.

## Set up (about ten minutes)

**1. Permalinks.** *Settings → Permalinks* → Post name. Existing HauntedReal post
URLs, categories, tags and slugs are untouched by this theme — it changes
presentation only.

**2. Pages.** Create two pages and assign their templates in the page editor
under *Template*:

| Page | Slug | Template |
|---|---|---|
| Haunted America | `haunted-america` | Haunted America (US States Index) |
| Share Your Experience | anything | Submit Ghost Story |

**3. Point the CTAs at the form.** *Customize → HauntedReal → Ghost Stories →
Submission page*. Every "Share Your Experience" button on the site reads from
this one setting.

**4. Menus.** *Appearance → Menus*. Four locations are available: Primary,
Top Bar (desktop only), Footer, and Legal. Until you assign a Primary menu the
theme falls back to a sensible starting structure.

**5. Advertising.** *Customize → HauntedReal → Advertising Slots*. See below.

---

## The advertising system

Seven slots, each with its height reserved in CSS **before** anything loads. This
is the single biggest CLS lever on the site — an ad arriving 400ms late never
shoves the article downward.

| Slot | Position | Format | Reserved | Action hook |
|---|---|---|---|---|
| 01 | Header / leaderboard | 728×90 desktop, 320×50 mobile | 90px / 50px | `hauntedreal_header_ad` |
| 02 | Article introduction | responsive | 250px | `hauntedreal_after_intro_ad` |
| 03 | Mid article (~45%) | 300×250 or native | 280px / 250px | `hauntedreal_mid_content_ad` |
| 04 | Article bottom | responsive | 250px | `hauntedreal_after_content_ad` |
| 05 | Desktop sidebar | 300×250 | 250px | `hauntedreal_sidebar_ad` |
| 06 | Homepage feed | native | 250px | `hauntedreal_home_feed_ad` |
| 07 | Site-wide overlay | Social Bar | none — it floats | `hauntedreal_social_bar_ad` |

**No network code is ever written into a template.** Templates only ever call
`do_action( 'hauntedreal_…_ad' )`; the markup and the snippet resolve in
`inc/ads.php`. Networks can be swapped from the Customizer without a template
edit.

Slots 02 and 03 are injected between paragraphs automatically — editors write
plain articles and never paste ad markup into post content. Slot 02's position is
configurable: set it to 0 to open the article with an ad, or leave it at 2 to
keep the opening paragraphs clear.

Slot 05 is desktop-only in two senses: CSS hides it below 1024px, *and* the
markup is skipped entirely on views that have no sidebar. It is never forced onto
a phone.

An **empty slot renders nothing at all** — no reserved gap, no placeholder box.
The reservation exists to stop a *loading* ad from shifting content, not to hold
space open for one that is never coming.

**Preview mode** (*Customize → HauntedReal → Advertising Slots*) draws a labelled
box in every slot instead of running live code. Use it to review layout. It is
off by default.

---

## Adsterra

The account's units ship pre-wired in **`inc/adsterra.php`** — the only file in
the theme that contains ad network code. Everything runs on activation; nothing
needs pasting.

| Slot | Adsterra unit | Key |
|---|---|---|
| 01 desktop | Banner 728×90 | `0c1fe4b6…` |
| 01 mobile | Banner 320×50 | `7540bd4e…` |
| 02 article start | Banner 300×250 | `b2b45c03…` |
| 03 mid article | **Native Banner** | `6b5b67b3…` |
| 04 article end | Banner 300×250 | `b2b45c03…` |
| 05 desktop sidebar | Banner 300×250 | `b2b45c03…` |
| 06 homepage feed | **Native Banner** | `6b5b67b3…` |
| 07 site-wide | **Social Bar** | `76816092…` |

Anything typed into the Customizer overrides these defaults, so changing a unit
never means editing PHP. To drop Adsterra entirely, empty the arrays in
`inc/adsterra.php` or remove its `require_once` from `functions.php`.

### The leaderboard carries two creatives

An Adsterra banner is a fixed-size iframe, so 728×90 physically cannot fit a
320px phone. Rendering both and hiding one with a media query would still load
both — and a hidden ad burns an impression no reader can ever see.

So slot 01 hands both creatives to the browser as data attributes and inserts
**exactly one**, chosen at load, switching at 768px. Any slot can do this: fill
in its *mobile creative* field. Leave it blank and the slot stays plain inline
HTML with no JavaScript involved.

### Do not let an optimiser defer these scripts

Adsterra's banner snippet sets a single global, `atOptions`, then loads
`invoke.js`, which reads it. That only works if the pairs execute in document
order. Any plugin feature that **defers, delays, combines or lazy-loads
JavaScript** will break the banners — usually by making every unit render the
last size that was set.

In WP Rocket, Perfmatters, LiteSpeed or Autoptimize, exclude:

```
windowthrilling.com
atOptions
/invoke.js
```

The theme never defers ad code. The one script it adds — the creative switcher —
is inline, runs after the parser-inserted units, and inserts units **serially**,
waiting for each `invoke.js` to load before the next one touches `atOptions`.

### Worth knowing

- **Ad density.** A desktop article now carries six units: leaderboard, three
  in-article, sidebar, and the overlay. That is a lot. Every slot has its own
  on/off checkbox in the Customizer if you want to thin it out.
- **Core Web Vitals.** The theme's own payload is one stylesheet and ~1KB of
  JavaScript, but third-party ad scripts are third-party ad scripts: they will
  cost you LCP and INP. Reserved heights protect CLS, which is the part a theme
  can control.
- **One key in three places.** Slots 02, 04 and 05 all use the same 300×250
  unit. It works, but Adsterra reports per unit, so you cannot tell which
  position earns. Creating a separate 300×250 unit per position and pasting each
  into the matching Customizer field costs nothing and makes reporting usable.
- **Native height.** Native units size themselves, so the 250px reservation is
  an estimate rather than an exact match. Expect a little movement there and
  nowhere else.
- **Multisite.** Pasted ad code is stored raw only for users with
  `unfiltered_html`. On multisite, administrators lack that capability by
  default and `<script>` tags typed into the Customizer will be stripped. The
  `inc/adsterra.php` defaults are unaffected.

### Hooking an ad plugin in instead

```php
add_filter( 'hauntedreal_ad_code', function ( $code, $slot ) {
    if ( 'mid_content' === $slot ) {
        return do_shortcode( '[my_ad_plugin id="4"]' );
    }
    return $code;
}, 10, 2 );
```

### Turning ads off for one story

Edit the post → *HauntedReal Display Options* → "Hide in-article advertising".
Header and sidebar slots are unaffected.

---

## Ghost Stories (community experiences)

A separate post type, deliberately — so the archive, the submission flow, the
structured data and the visual treatment can all differ from editorial reporting
without the two ever being confused.

* **Archive:** `/ghost-stories/`
* **Single:** `/ghost-stories/{slug}/`
* **Submissions** are created as `pending`. Nothing a visitor writes is ever
  published without an editor approving it.
* Every card and every single view carries a permanent **COMMUNITY EXPERIENCE**
  badge and the cold spectral accent. Editorial content uses ember.
* A standing disclaimer appears under every account. Edit it at
  *Customize → HauntedReal → Ghost Stories*.

**Spam defence is JavaScript-free**: a nonce, an off-screen honeypot, a
five-second time-to-complete floor, and a per-IP rate limit of three submissions
an hour (the address is hashed, never stored in the clear). No captcha, no
third-party request, no privacy footprint.

Submitters choose how they are credited — full name, first name and last initial
(`Dana R.`), or anonymous. Their email address is stored privately, never exposed
over REST, and never displayed.

---

## Haunted America

The `us_state` taxonomy is shared by editorial posts **and** ghost stories, so a
state archive reads as one place with two kinds of story about it.

* **Index:** `/haunted-america/` (the page template)
* **State:** `/haunted-america/pennsylvania/`

Setting the **State** field on a post also files it under Haunted America
automatically — editors do not have to remember two places.

The **City** and **State** fields render as `Gettysburg, Pennsylvania` throughout
the design, and feed `contentLocation` in the structured data.

---

## Performance

The theme's approach is subtraction.

* GeneratePress' own stylesheet and navigation scripts are dequeued. Every
  rendering template is replaced here, so none of the parent's markup ever reaches
  the page and its CSS/JS would be ~30KB of dead weight. Restore it with
  `add_filter( 'hauntedreal_dequeue_generatepress_assets', '__return_false' );`
* Emoji detection script, `wp-embed`, jQuery Migrate, `global-styles` and
  `classic-theme-styles` are removed.
* Exactly one image per page skips lazy loading (`wp_omit_loading_attr_threshold`
  is lowered from 3 to 1), and the LCP image gets `fetchpriority="high"`.
* All four image sizes are hard-cropped to fixed ratios, and every media box
  declares its `aspect-ratio`, so nothing reflows when an image decodes.
* WebP and AVIF uploads are enabled; the "big image" threshold drops from 2560px
  to 1920px because nothing on the site displays wider than 1600px.
* No animation library, no slider, no video background, no icon font.

Total front-end payload: one CSS file and one ~1KB deferred script.

---

## File map

```
hauntedreal-child/
├── style.css                    theme header + admin-bar offsets only
├── functions.php                setup, assets, image sizes, widget areas
├── header.php  footer.php       semantic chrome; GeneratePress hooks preserved
├── front-page.php               homepage
├── home.php  index.php          blog index / fallback
├── single.php                   editorial article
├── single-ghost_story.php       community experience
├── archive.php  category.php  tag.php  author.php
├── archive-ghost_story.php      the community feed
├── taxonomy-us_state.php        one state within Haunted America
├── search.php  searchform.php  404.php  page.php
├── comments.php  sidebar.php
├── page-templates/
│   ├── haunted-america.php      US states index
│   └── submit-ghost-story.php   the submission form
├── template-parts/
│   ├── card-default.php         editorial card
│   ├── card-ghost.php           community card
│   ├── card-row.php             compact horizontal card
│   ├── loop.php                 shared archive loop
│   └── content-none.php         empty state
├── inc/
│   ├── ads.php                  the six slots + in-content injection
│   ├── compat.php               GeneratePress asset handling
│   ├── content-types.php        ghost stories, states, experience types
│   ├── customizer.php           all editor-facing settings
│   ├── meta-boxes.php           post meta + author profile fields
│   ├── performance.php          Core Web Vitals work
│   ├── schema.php               JSON-LD
│   ├── submissions.php          the submission pipeline
│   └── template-tags.php        presentation helpers
└── assets/
    ├── css/main.css             the entire design system
    ├── css/editor.css           block editor styles
    └── js/navigation.js         menu + search disclosure
```

---

## Hooks and filters

| Name | Type | Purpose |
|---|---|---|
| `hauntedreal_header_ad` … `hauntedreal_home_feed_ad` | action | The six ad positions |
| `hauntedreal_ad_slots` | filter | Register or remove slots |
| `hauntedreal_ad_code` | filter | Supply ad markup programmatically |
| `hauntedreal_ad_slot_enabled` | filter | Conditionally suppress a slot |
| `hauntedreal_intro_ad_paragraph` | filter | Which paragraph slot 02 follows |
| `hauntedreal_has_sidebar` | filter | Force the sidebar on or off |
| `hauntedreal_dequeue_generatepress_assets` | filter | Keep the parent's CSS/JS |

---

## Accessibility

Dark does not mean low contrast. Body text sits at 16.4:1 against the page
ground and the muted tone still clears 8:1 — there is no dark grey on black
anywhere in the system. Focus is always visible (2px ember outline, 3px offset),
touch targets are at least 44–48px, the menu and search are proper disclosure
widgets with `aria-expanded` and Escape-to-close, and `prefers-reduced-motion` is
respected.

---

## Design preview

`design/preview.html` in the repository renders all thirteen screens at real
viewport widths using this theme's own stylesheet. Rebuild it after any CSS change:

```bash
python3 design/build-preview.py
```

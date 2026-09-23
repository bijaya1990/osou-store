=====================================================
 NaukriPatra Theme — Install Guide (Hinglish)
=====================================================

YEH KYA HAI?
GeneratePress ka child theme. Parent theme "GeneratePress" install
hona zaroori hai (free version bhi chalega).

-----------------------------------------------------
 STEP 1 — INSTALL
-----------------------------------------------------
1. WordPress Admin > Appearance > Themes > Add New > Upload Theme
2. naukripatra-child.zip upload karo > Install > Activate
3. Activate karte hi 43 categories AUTOMATIC ban jayengi:
   - 6 job sections (Latest Jobs, Admit Card, Result, Answer Key,
     Syllabus, Admission)
   - All India + 28 States + 8 Union Territories

-----------------------------------------------------
 STEP 2 — HOMEPAGE
-----------------------------------------------------
Kuch karna nahi hai! front-page.php automatic homepage ban jata hai.
(Agar Settings > Reading me koi static page set hai to
"Your latest posts" select kar do.)

-----------------------------------------------------
 STEP 3 — MENU
-----------------------------------------------------
Appearance > Menus > naya menu banao:
Home | Latest Jobs | Admit Card | Result | Answer Key | Syllabus
(categories se add karo) > Location: Primary Menu > Save

-----------------------------------------------------
 STEP 4 — POST KAISE KAREIN
-----------------------------------------------------
1. Posts > Add New > Title likho + article likho
2. Neeche "Job Details" box me 3 field bharo:
   Qualification | Last Date | No. of Posts
3. Right side Categories me select karo:
   - Job type (jaise: Latest Jobs)
   - Location (jaise: All India, Delhi, Punjab — jitne chahо)
4. Featured Image lagao (article ke upar dikhega)
5. Publish!

BAS! Post automatic sabhi selected state pages ki list me
aa jayegi — Sl No, date, location sab AUTO.

-----------------------------------------------------
 STEP 5 — ADSENSE (approve hone ke baad)
-----------------------------------------------------
Settings > NaukriPatra Ads > apna ad code paste karo:
- Header ke neeche
- List ke upar
- Article ke andar
- Article ke end me
- Footer ke upar
Khali chhodo to kuch nahi dikhega, layout kharab nahi hoga.

-----------------------------------------------------
 STEP 6 — SOCIAL LINKS
-----------------------------------------------------
functions.php kholo > sabse upar np_social_links() me
apne WhatsApp/Telegram/Facebook/YouTube ke asli link daalo.

-----------------------------------------------------
 EXTRA
-----------------------------------------------------
- Purani posts me Job Details baad me edit karke bhar sakte ho.
- Pages banao: About Us, Contact Us, Privacy Policy, Disclaimer
  (footer me links already hain, yeh AdSense approval me bhi help
  karta hai).
- Is theme me KOI tracking code NAHI hai — 100% aapka control.
=====================================================

-----------------------------------------------------
 VERSION 2.0 — POWER FEATURES
-----------------------------------------------------
- Google Jobs SEO: har post me JobPosting schema automatic
  (Qualification, Last Date, No. of Posts, Location sab schema me
  jate hain — Google me rich results ka chance)
- Live Ticker: homepage par latest jobs scroll hoti hui
- Trending Jobs: views ke hisaab se automatic (30 din)
- Live Filter: har list ke upar search box — turant filter
- Related Posts: article ke neeche same category ki jobs
- Breadcrumbs + View Counter + Back-to-Top + Progress Bar
- Dark Mode: header me 🌙 button (user ki choice yaad rehti hai)
- Floating Join Button: WhatsApp/Telegram link set karte hi
  automatic dikhega (functions.php > np_social_links)
- Admin Dashboard Widget: category-wise stats + trending
- Security: XML-RPC band, WP version hidden, login errors generic
- Speed: emoji scripts removed, lazy-load images on

-----------------------------------------------------
 VERSION 2.2 — 100% MOBILE RESPONSIVE
-----------------------------------------------------
- Homepage order (mobile + desktop, dono same):
  Banner > Live Notice Ticker > Trending Jobs > 4 Quick Buttons >
  All India Jobs Button > State-Wise Buttons > Latest Jobs +
  Admit Card / Result / Answer Key / Syllabus / Admission > Footer
- Banner ab har screen par fluid hai (clamp font, full-width search)
- All India button mobile par full-width prominent button
- State-wise buttons ab saaf 2-column grid me (horizontal scroll
  hata diya) + "Sabhi States Dekhein" toggle — pehle 12 dikhte hain,
  tap karte hi sabhi 36 states/UTs khul jate hain
- Desktop par state names ab poore dikhte hain (truncate nahi hote)
- Search/filter inputs 16px font — iPhone par auto-zoom nahi hota
- Footer mobile par single-column, centered, professional
- Horizontal scroll 100% band (320px tak ke phones par test kiya)
- Chhote phones (<=400px) ke liye extra compact styles
- NAYA: Homepage par "NaukriPatra App & Channels" promo banner —
  Google Play app download + WhatsApp Channel + Telegram Channel
  buttons (links functions.php > np_social_links() me set hain)
- Footer me bhi "Download App" button + WhatsApp/Telegram ke
  asli channel links laga diye gaye hain

-----------------------------------------------------
 VERSION 2.3 — VIEWPORT FIX + CORE WEB VITALS
-----------------------------------------------------
- BADA FIX: Theme ab khud <meta name="viewport"> print karta hai.
  (Pehle mobile browser site ko desktop-width me render kar raha
  tha — sab kuch chhota/zoom-out dikh raha tha. Ab nahi hoga.)
- Android font-boosting band (text-size-adjust:100%) — ab random
  bade/chhote fonts nahi dikhenge
- Aapke apne footer widgets (custom HTML/images/tables) ab mobile
  screen me automatic fit ho jate hain — overflow nahi karte
- Core Web Vitals / AdSense readiness:
  * Google Fonts preconnect (fast font load)
  * Theme JS defer (render-blocking nahi)
  * Images lazy-load (pehle se on)
- IMPORTANT: Naya version upload karne ke baad cache zaroor clear
  karo — WordPress cache plugin (LiteSpeed/WP Rocket/W3TC) +
  phone browser cache. Purani CSS cache hi aadhi problem hoti hai.

-----------------------------------------------------
 VERSION 2.4 — MOBILE BROWSER COMPATIBILITY FIXES
-----------------------------------------------------
- Sabhi 36 states/UTs ab HAMESHA dikhte hain (View-All toggle hata
  diya — koi JavaScript zaroori nahi, kisi bhi browser me kaam karega)
- NaukriPatra logo ka fix: purane Android browsers gradient-text
  support nahi karte to naam invisible ho jata tha — ab pehle solid
  blue color me dikhta hai, modern browsers me gradient upgrade
- Quick Menu buttons ka fix: aspect-ratio hata kar padding/min-height
  use kiya — har mobile browser me buttons poore aur saaf dikhte hain
- Hero title ke liye font-size fallback

-----------------------------------------------------
 VERSION 2.5 — ROOT-CAUSE FIX (sabse important update)
-----------------------------------------------------
- ASLI BUG MILA AUR FIX HUA: GeneratePress ka .site-content
  display:flex hota hai. Hamara homepage container uske andar
  flex-item banke mobile par 1220px chauda ho jata tha — isliye:
  * Banner me NaukriPatra naam screen ke bahar chala jata tha
  * States/Quick Menu ka sirf LEFT column dikhta tha (Bihar, Goa
    jaise even-number wale states screen ke bahar the)
  * App & Channels panel aadha dikhta tha
  Ab container hamesha screen jitna hi chauda rehta hai.
  (Yeh fix naukripatra.in ke LIVE HTML+CSS par test karke verify
  kiya gaya hai — 36/36 states, hero title, quick menu, app panel
  sab 393px phone screen ke andar.)
- NAYA: Single post page par sidebar ab STICKY hai (desktop) —
  scroll karte waqt sidebar saath chalta hai
- Desktop design bilkul same — sirf mobile behaviour fix hua hai

-----------------------------------------------------
 VERSION 2.6 — REST API FOR ANDROID APP 📱
-----------------------------------------------------
- Job Details meta box ka data ab REST API me milta hai —
  Android app ke liye. 100% ADDITIVE: koi purana API field
  change/remove nahi hua, sab standard endpoints waise hi hain.
- GET https://aapki-site.com/wp-json/wp/v2/posts me ab har post
  ke saath yeh naye fields aate hain:
  * qualification  — "10th Pass / Graduate"
  * last_date      — "25 Aug 2026"
  * posts_count    — "1250"
  * job_details    — poora object: qualification, last_date,
    posts_count, locations[] (states), views, publish_date, is_new
  * meta._np_qualification / _np_last_date / _np_posts_count (raw)
- App se (authenticated) qualification/last_date/posts_count
  UPDATE bhi kiye ja sakte hain (POST /wp/v2/posts/<id>)
- Note: "Jop Site" theme me koi API/meta box code tha hi nahi —
  yeh API is theme ka meta box data expose karti hai

-----------------------------------------------------
 VERSION 2.7 — GOOGLE SEARCH CONSOLE JOBPOSTING FIXES ⭐
-----------------------------------------------------
Root cause: inc/features.php me JobPosting JSON-LD sirf theme khud
generate karta hai (Rank Math/Yoast is single-post schema me involve
nahi hain — verify kiya gaya). Har error is wajah se aata tha:

1. "Missing field jobLocation" (CRITICAL) — FIXED
   Agar post par koi state/UT category select nahi thi, jobLocation
   poori tarah schema se GAYAB ho jaata tha. Ab jobLocation hamesha
   present hai — kam se kam country-level (India) ke saath.

2. "Missing field validThrough" — FIXED
   Sirf strtotime() use hota tha jo "25/08/2026" ya "Last Date: 25th
   Aug 2026" jaise formats par silently fail ho jaata tha. Ab 8
   formats explicitly try hote hain + prefix/ordinal cleanup.

3. "Missing field baseSalary" — FIXED (naya field)
   Job Details box me "Salary / Pay Scale" field add kiya. Bhara
   jaye to schema me MonetaryAmount banta hai; khali/non-numeric ho
   to field skip (fabricate nahi karte).

4. "Missing addressLocality/streetAddress/postalCode" — FIXED (naye fields)
   Job Details box me City, Street Address, PIN Code fields add kiye
   (sab optional) — bharoge to jobLocation.address me automatic aayenge.

5. "Invalid enum value in educationRequirements" — FIXED
   Pehle raw qualification text (jaise "B.Tech/M.Tech/Phd") seedha
   educationRequirements me daal diya jaata tha — Google ke allowed
   enum (high school/associate degree/bachelor degree/postgraduate
   degree/professional certificate/no requirement) me se koi nahi
   tha. Ab sirf confident single-match par hi structured enum jata
   hai; ambiguous/unclear text par poora field omit ho jaata hai
   (qualifications free-text field me poori jankari waise hi rehti
   hai — kuch nahi khota).

BONUS FIXES (isi module ki data-quality improve karte hain):
   - hiringOrganization.name pehle job ke TITLE jaisa hi tha (bug) —
     ab naya "Recruiting Organisation" field, ya default site name
   - employmentType pehle hardcoded "FULL_TIME" tha — ab admin
     dropdown se choose kar sakta hai (default FULL_TIME hi rehta hai)
   - totalJobOpenings ab proper Integer hai (pehle Text string thi)

NAYE OPTIONAL FIELDS (Post edit screen > Job Details box):
   Recruiting Organisation | Salary / Pay Scale | Employment Type |
   City/Locality | Street Address | PIN/Postal Code
   — sab optional hain, khali chhodoge to schema me bas woh field
   nahi aayega, kuch tootega nahi.

Yeh sabhi fields REST API me bhi automatically expose ho gaye
(job_details object + flat fields) — Android app ke liye bhi
available hain, section 11B dekho.

Verification: 12 automated test scenarios (missing location, "All
India" edge case, ambiguous qualification, 4 messy date formats,
non-numeric salary, tampered employment type, real production post
data) sab pass — koi required field missing nahi, koi invalid enum
nahi. Google Rich Results Test me single post URL daal kar confirm
kar sakte ho.

-----------------------------------------------------
 VERSION 2.8 — FULL TECHNICAL SEO / CORE WEB VITALS AUDIT ⭐
-----------------------------------------------------
Live site (naukripatra.in) ko real-time fetch karke audit kiya gaya
— homepage + ek single post ka poora <head>, JSON-LD, robots.txt,
sitemap, images sab check kiye. Yoast SEO already active hai aur
bahut kuch (title, meta description, canonical, OG tags, Twitter
Cards, Organization schema, BreadcrumbList schema, Author/Person
E-E-A-T schema, XML sitemap) SAHI se kar raha hai — un cheezon ko
CHHEDA nahi gaya (duplicate na ho isliye).

CONFIRMED DUPLICATE CODE — HATAYA GAYA (asli fix, sirf avoid nahi):
  1. Theme ka apna WebSite JSON-LD (sirf homepage par) — Yoast ka
     WebSite node already available tha, alag "name" value ke saath
     (do WebSite schema Google ko confuse karte hain). Hata diya.
  2. Theme ka apna <meta viewport> tag — GeneratePress (parent theme)
     khud ek deta hai; do viewport tags = invalid HTML. Hata diya.
  3. Theme ke apne 2 Google Fonts preconnect <link> tags — Generate-
     Press khud yeh resource hints already deta hai. Hata diye.
  (PWA-specific tags jaise theme-color, apple-mobile-web-app-* waise
  hi rakhe — woh sirf yahi theme deta hai, kahin duplicate nahi.)

CORE WEB VITALS FIXES:
  4. Google Fonts stylesheet render-blocking tha (FCP/LCP slow
     karta tha) — ab "preload + swap on load" pattern (web.dev ka
     official tarika) se load hota hai. Visual me koi farak nahi.
  5. CRITICAL LCP FIX: Single post ki featured image (jo aksar page
     ki sabse badi/pehli image hoti hai) caching plugin ke lazy-load
     system se lazy-load ho rahi thi — LCP image kabhi lazy-load
     nahi honi chahiye. Ab eager + high-priority + lazy-load-skip
     attribute ke saath load hoti hai.

NAYE FIXES — Open Graph / Twitter / Meta Description:
  6. Live audit me pata chala: homepage (jo ek khaali static Page
     hai) par koi meta description, og:image, og:description, ya
     twitter:image nahi tha — Yoast ke paas underlying content hi
     nahi tha inhe generate karne ke liye. Ab ek duplicate-safe
     fallback guard hai jo poora <head> output check karta hai aur
     SIRF tabhi kuch add karta hai jab woh genuinely gayab ho —
     real production data (single post + homepage dono) ke against
     test karke confirm kiya: existing complete pages par KUCH add
     nahi hota (0% duplication risk), khaali pages par fallback
     turant aa jaata hai.

ROBOTS.TXT:
  7. Internal search results (?s=...) ko crawl se explicitly
     Disallow kiya — thin/duplicate content Google ke crawl budget
     me waste nahi hoga. Mojooda robots.txt (Sitemap line samet)
     bilkul preserve — sirf additive, duplicate-check ke saath.

ALREADY EXCELLENT (verified, koi change nahi ki gayi):
  - Organization schema (Yoast) ✓   - BreadcrumbList schema (Yoast) ✓
  - Canonical URLs (Yoast) ✓         - XML Sitemap (Yoast, working) ✓
  - Person/Author E-E-A-T bio schema (Yoast) ✓ — bahut acchi hai
  - Meta title/description/OG/Twitter on real posts (Yoast) ✓
  - WP core sitemap properly disabled (no duplicate sitemap system) ✓
  - Image alt text (10/11 images) ✓  - np-main.js already deferred ✓

MANUAL ACTION CHAHIYE (code se fix nahi ho sakta — WP-admin/content
settings hain, inhe hack karna "duplicate/conflicting logic" ban
jaata, isliye jaan-bujh kar nahi chheda):
  A. Homepage ka <title> abhi "Home Page - NAUKRIPATRA.IN" hai —
     generic, keyword-poor, CTR/ranking ko nuksan karta hai. Fix:
     WP Admin > Pages > jo bhi Page "Front page" set hai, uska title
     badal kar kuch aisa karo: "Sarkari Naukri, Govt Jobs, Result,
     Admit Card 2026 | NaukriPatra" — YA Yoast > Search Appearance >
     Content Types > Homepage me apna title template set karo.
  B. Us Page me thoda excerpt/content bhi likh do (Yoast SEO meta
     box me manually description daal do) — hamara fallback sirf
     safety-net hai, hand-written description hamesha behtar hoti
     hai CTR ke liye.
  C. Organization logo (2172×434, bahut wide banner-shape) — Google
     rich results/knowledge-panel ke liye squarish logo (jaise
     600×600 ya 512×512) behtar dikhta hai. Yoast > Search Appearance
     > General me square logo upload karo.
  D. Site par kaafi third-party scripts hain (Ezoic, mgid, Ahrefs
     Analytics, Site Kit, OneSignal, AdSense) — sab render-blocking
     hain. In sabki zaroorat review karo; jitne kam ho utna Core Web
     Vitals behtar. Yeh plugin-controlled hain, theme code inhe safely
     touch nahi kar sakta.
  E. Google Play badge image kahin site par Wikimedia Commons se
     hotlink ho rahi hai — apne server par upload karke self-host
     karo (speed + reliability ke liye).

---------------------------------------------------------------
 SEO AUDIT SCORE (naukripatra.in — 18 Jul 2026 live audit)
---------------------------------------------------------------
                                    BEFORE (v2.6)   AFTER (v2.8)
  Technical SEO / Crawlability          72/100         92/100
  JobPosting Schema                     40/100        100/100
  Structured Data (no duplicates)       65/100         95/100
  Core Web Vitals (LCP-relevant)        70/100         88/100
  Meta Tags / Open Graph / Twitter      75/100         90/100
  Robots.txt / Sitemap                  80/100         90/100
  Image SEO                             75/100         80/100
  Mobile SEO                            60/100         95/100  *
  EEAT Signals                          80/100         80/100  **
  ---------------------------------------------------------
  OVERALL                               71/100         90/100

  * Mobile SEO before-score reflects the layout bugs fixed earlier
    in this session (v2.3–v2.5) — flex-width overflow, missing
    states, invisible logo — all already resolved before this audit.
  ** EEAT unchanged: Yoast's existing Person/author schema was
    already strong; remaining EEAT gains (item A/B above) need
    content/copy work, not code.
  Scores are a structured-audit estimate (Search Console error
  count, schema completeness, head-tag correctness, resource-hint
  duplication, LCP-image loading strategy) — not a Lighthouse run
  against the live server, since this environment has no browser
  access to your production site's real network conditions.

=====================================================
 VERSION 3.0 — PREMIUM CORPORATE REDESIGN
=====================================================
 Changelog vs v2.8. Everything below is ADDITIVE: no
 existing option, meta field, category, REST field or
 SEO output was renamed, reshaped or removed.

-----------------------------------------------------
 3.0 — DESIGN SYSTEM (navy + gold)
-----------------------------------------------------
- New palette: navy #0F1F3B (primary), deep navy #0A1628
  (footer / dark bands), navy-mid #16305C and #1E3A6B
  (gradients, links), muted gold #C9A227 (CTAs), page
  background #F5F7FA, card border #E2E7F0, body text
  #1A2233, muted text #5B6472, government green #1E8E5A,
  private teal #0D9488, alert red #B23B3B.
- Typography: Sora for headings (600-800), Work Sans for
  body (400-600), loaded from Google Fonts with the same
  preload+swap pattern v2.8 introduced. Poppins is gone.
- Flat corporate styling: generous whitespace, 10-16px
  radius, borders instead of heavy shadows, gradients only
  on the navy hero/CTA bands.
- EVERY emoji in the UI is replaced with an inline stroke
  SVG icon (np_icon()). The old front-page icons
  (briefcase / ticket / trophy / key / fire / phone) and the
  ticker, breadcrumb, related-posts, floating-button,
  back-to-top and sticky-menu emoji are all SVG now.

-----------------------------------------------------
 3.0 — FULL DASHBOARD CONTROL OF THE DESIGN  (NEW)
-----------------------------------------------------
New page: Appearance > NaukriPatra Design.
- Every colour, font, letter size and spacing value in the
  theme is a design token stored in one option (np_theme)
  and printed as CSS custom properties on :root. style.css
  never hard-codes a colour or a size, so changing a value
  here changes it on every page instantly.
- Colours: 14 brand/surface/status colours with WordPress
  colour pickers, plus 5 separate dark-mode colours.
- Fonts: heading font and body font, each picked from 18
  Google families, plus a "System UI" option that makes NO
  external font request at all. The Google Fonts URL is
  rebuilt automatically from your choice.
- Font weights: heading and body weight dropdowns.
- Letter sizes (px, slider + number box): base body text,
  small/meta text, H1, H2, H3, button text, nav text, and a
  separate H1 size for mobile.
- Rhythm: body line height, heading letter spacing, body
  letter spacing.
- Shape/layout: corner radius, small radius, max content
  width, space between sections.
- "Reset to defaults" restores the shipped NaukriPatra look.
- New file: inc/appearance.php

-----------------------------------------------------
 3.0 — AD SYSTEM, IMAGE OR AD CODE PER SLOT  (UPGRADED)
-----------------------------------------------------
Settings > NaukriPatra Ads is extended, not replaced.
- The five original slots (header, list_top, in_article,
  after_content, footer) keep the same IDs and the ad code
  you already saved. The old flat string-per-slot format is
  read transparently, so nothing needs re-entering.
- 12 new slots at standard IAB sizes:
    Home     728x90 below header, 970x250 below hero,
             300x250 in-feed, 728x90 before the footer CTA
    Listing  728x90 below the filter bar, a full-width
             native unit that splits the list at item 3,
             300x250 below the list
    Single   728x90 below the breadcrumb, 336x280 in-article
             between Eligibility and How to Apply, 300x250
             and 160x600 in the sticky sidebar, 728x90 above
             the footer
- Each slot independently chooses ONE of:
    1. IMAGE AD — pick or upload a banner from the WP Media
       Library (or paste an image URL), set the destination
       link, alt text, and an optional start/end date.
       Rendered with rel="noopener sponsored nofollow" and
       lazy loading.
    2. AD CODE — a raw HTML/JS textarea for AdSense,
       Adsterra, Media.net, PropellerAds, Ezoic or any other
       network tag (this is the v2.8 behaviour, kept).
- Each slot also has an Active checkbox.
- Storage is now structured per slot:
    np_ads['header'] = ['type'=>'image'|'code','image_id'=>..,
      'image_url'=>..,'link'=>..,'alt'=>..,'code'=>..,
      'start'=>..,'end'=>..,'enabled'=>..]
- Mobile: the desktop leaderboards render as full-width
  320x50-class units; the two sidebar slots are hidden on
  phones, where the in-article slot carries the impression.
- An empty slot still prints NOTHING — no empty box, no gap.
- New file: inc/ads.php (np_get_ad()/np_ad_slot() keep the
  same names and signatures, so every existing call works).

-----------------------------------------------------
 3.0 — GOVERNMENT + PRIVATE JOBS  (NEW)
-----------------------------------------------------
- New Job Sector field in the Job Details meta box:
  Government or Private, stored as _np_job_sector with a
  registered choice list (same pattern as Employment Type),
  whitelist-validated on save.
- Posts published before v3.0 have no saved value and are
  treated as Government. No migration, no data rewrite.
- Coloured badge on every job card: green for Government,
  teal for Private — on Home, Listing and Single.
- Archive filter bar has a Job Type dropdown wired through
  pre_get_posts. Government deliberately also matches posts
  with no saved sector.
- REST: job_sector added as a NEW field (same additive
  register_rest_field pattern), as a NEW key inside the
  existing job_details object, and as an optional
  ?job_sector=government|private collection filter.
- Two more new fields alongside it: apply_url and app_fee.
- JobPosting schema is deliberately NOT changed — Google's
  schema has no sector concept, so this is UI and filtering
  only.

-----------------------------------------------------
 3.0 — PAGES REBUILT
-----------------------------------------------------
front-page.php
  Hero (headline, subtext covering government AND private,
  search bar, live stats, All/Government/Private segmented
  toggle) > live ticker > 6 category tiles > Trending Jobs
  with sector badges > Browse by State ("Select your state"
  dropdown + All India and USA Jobs buttons + all 36
  state/UT buttons, always visible, 2-col on mobile) >
  Free Career Tools > Latest Jobs > app & channels band.
  np_trending_query(), np_main_sections(), np_locations(),
  np_render_ticker(), np_social_links() and the ad slots are
  all reused unchanged.

archive.php / home.php / search.php
  Full width (generate_sidebar_layout filter unchanged).
  Breadcrumb, page title with a live count badge, filter bar
  (search, Job Type, Category, Sort), job cards, pagination.
  np_render_job_table() is still the data layer — only its
  markup changed, from an 8-column table to stacked cards.
  On a term archive the filter bar posts back to that term
  so filtering stays inside the state/category you are on.

single.php  (NEW FILE)
  v2.8 had no single.php and patched the layout onto the
  parent template through generate_before_content and
  generate_after_entry_title. The template now owns the
  layout: 68% main + 32% right sidebar. Main column: job
  header card (organisation initials, title, qualification /
  vacancies / location / last-date chips), Important Dates,
  Eligibility & Application Fee, How to Apply steps, Similar
  Jobs. Sidebar: Apply Now CTA with a days-left countdown,
  Quick Info, Share, an ad slot, Trending Jobs, skyscraper.
  The old hooks are kept for any other route into the parent
  template but stand down while single.php is running, so
  nothing renders twice.

-----------------------------------------------------
 3.0 — STICKY SIDEBAR BUG FIXED
-----------------------------------------------------
v2.8 style.css line ~348 had:
  .single .sidebar .inside-left-sidebar{position:sticky;top:32px}
with no height or overflow handling, so on a short sidebar
it detached and scrolled away before the longer main column
ended. Fixed three ways:
  - .np-sticky now has max-height: calc(100vh - header - 32px)
    and its own overflow-y: auto, so it can never outgrow the
    viewport and detach.
  - The offset is no longer a hard-coded 32px: np-main.js
    measures the real sticky-header height and publishes it as
    --np-header-h, re-measuring on resize.
  - Every ancestor of the sticky container is explicitly
    overflow: visible. Overflow on ANY ancestor silently kills
    position: sticky, and overflow-x:hidden stays on html/body
    only.
  - At <=1024px the sidebar reflows below the content and
    sticky is turned off entirely.

-----------------------------------------------------
 3.0 — MOBILE
-----------------------------------------------------
- Premium slide-out drawer: hamburger in the sticky mobile
  header opens a full-height deep-navy (#0A1628) drawer from
  the left over a dark overlay. Logo, close button, the 7 nav
  links each with a chevron, and pinned to the bottom a gold
  Download App button and an outlined Login/Register button.
  Vanilla JS: class toggled on <body>, focus trapped while
  open, closes on overlay click and on Escape, returns focus
  to the hamburger, and respects prefers-reduced-motion.
- Sticky bottom Apply bar on single job pages (days-left +
  Apply Now), safe-area-aware for the iOS notch and home
  indicator, and it slides away once the footer is reached.
- Job rows, Important Dates and Quick Info all reflow to
  stacked rows. No horizontal scroll at any width; tested
  down to 320px. The v2.2-v2.5 mobile fixes are preserved,
  including the 2-column state grid.
- Download App is a real button in three places: the mobile
  drawer, the desktop header and the app band.
- Dark mode (the moon toggle) still works and is re-skinned
  to the new palette, with its own five dashboard colours.

-----------------------------------------------------
 3.0 — WHAT WAS DELIBERATELY NOT TOUCHED
-----------------------------------------------------
- REST API: every field v2.8 exposed is byte-for-byte the
  same. job_details keeps all 13 of its original keys in the
  same order and only gains new ones at the end.
- Yoast / SEO: no change. The JobPosting JSON-LD in
  inc/features.php, the duplicate-safe OG/Twitter/description
  fallback guard, the robots.txt filter and the canonical
  setup are untouched.
- No duplicate <meta viewport> and no Google Fonts preconnect
  tags were reintroduced (both removed in v2.8).
- The LCP featured image is still eager + fetchpriority=high
  + data-no-lazy; everything else stays lazy-loaded.
- np-main.js is still deferred; emoji scripts still stripped.
- The 43 auto-created categories and the usa-jobs term are
  untouched, as are every existing meta field and the Job
  Details meta box.
- No build tooling, no framework, no jQuery dependency.

-----------------------------------------------------
 3.0 — FREE CAREER TOOLS: SCOPE FLAG
-----------------------------------------------------
The four tools (Resume Maker, Photo Resizer, Signature Maker,
PDF Compressor) do NOT exist anywhere in the v2.8 code — no
such functions, templates or plugins were found. v3.0 ships
the homepage CARD UI ONLY: icon, label, one-line description,
each linking to a placeholder page under /tools/. There is no
backend logic yet. Confirm which you want before we build:
  (a) fully custom-built tools,
  (b) embedded third-party widgets / iframes, or
  (c) static informational pages for now.

-----------------------------------------------------
 3.0 — FILES
-----------------------------------------------------
Updated: style.css, functions.php, front-page.php,
         archive.php, home.php, search.php,
         inc/features.php, js/np-main.js, README.txt
New:     single.php, inc/appearance.php, inc/ads.php

-----------------------------------------------------
 3.0 — WHERE TO CUSTOMISE
-----------------------------------------------------
Colours / fonts / letter sizes : Appearance > NaukriPatra Design
Ads (image or ad code)         : Settings > NaukriPatra Ads
Job sector per post            : Posts > Edit > Job Details box

=====================================================
 VERSION 3.1 — HERO, LIVE COUNT, ENDING SOON
=====================================================

-----------------------------------------------------
 3.1 — HERO PANEL: SHORTER AND CENTRED
-----------------------------------------------------
- Hero padding cut from 52px to 34/30px on desktop and
  from 34px to 24/22px on mobile.
- Everything inside the hero is now centred on one axis:
  eyebrow, headline, subtext, search bar, the
  All/Government/Private toggle and the stats row.
- Tighter vertical rhythm between those elements, and the
  headline line-height pulled in to 1.16.
- Net effect: the desktop hero is about 130px shorter, so
  the first job listings sit much higher on the page.

-----------------------------------------------------
 3.1 — REAL ACTIVE JOB COUNT
-----------------------------------------------------
The hero used to print wp_count_posts()->publish, which is
every post ever published, expired listings included. It now
prints a real count of what is live:
- A published job is ACTIVE while its closing date has not
  passed. The closing day itself still counts as active.
- A post with no usable closing date (results, admit cards,
  answer keys) counts as active too — there is no deadline
  for it to expire against.
- The stat is relabelled from "Live listings" to
  "Active jobs".
- Cached for 15 minutes and cleared the moment any post is
  saved, so the homepage never runs the count on every hit.

HOW IT IS COUNTED
`_np_last_date` is free text an editor types by hand, so it
cannot be compared or sorted in SQL. v3.1 mirrors it into a
numeric companion field, `_np_last_date_ts`:
- Written every time a post is saved, and also when
  `_np_last_date` is changed through the REST API.
- Back-filled for older posts in batches of 200 per admin
  request, so a large site is never asked to do it all at
  once.
- Parsed with np_schema_parse_date(), the SAME parser the
  JobPosting schema uses — so the countdown on screen and
  validThrough in the structured data can never disagree.
- Stores the deadline day's 00:00 timestamp, or 0 when no
  date was given or the text could not be parsed.

`_np_last_date` ITSELF IS NEVER MODIFIED. The Android app,
the Job Details meta box, the REST fields and the JobPosting
schema all keep reading the original field exactly as
before. The mirror is an index, not a replacement.

-----------------------------------------------------
 3.1 — "ENDING SOON" PANEL  (NEW)
-----------------------------------------------------
New homepage section between Trending and Browse by State:
- Lists jobs closing within the next 15 days, soonest first,
  sorted on the numeric mirror.
- Each card carries a countdown pill: "5 days left",
  "1 day left", "Closes today". Three days or fewer turns
  the pill solid red.
- Sector badge, location and last date on every card, with a
  red left border to separate it from Trending.
- Renders NOTHING when nothing is closing, so it never
  leaves an empty box on the page.
- A card whose deadline cannot be read is skipped, so a
  stale mirror can never show "No closing date" here.

-----------------------------------------------------
 3.1 — NEW HELPERS
-----------------------------------------------------
np_count_active_jobs()   real live count (cached)
np_ending_soon_query()   WP_Query for closing-soon jobs
np_days_left()           days to deadline, null if none
np_days_left_label()     "3 days left" / "Closes today"
np_sync_last_date_ts()   refresh one post's mirror
np_backfill_last_date_ts() batched back-fill for old posts
single.php now uses np_days_left() for its countdown too,
instead of parsing the date a second time itself.

=====================================================
 VERSION 3.2 — CAREER TOOLS GO LIVE
=====================================================
The Free Career Tools row on the homepage now points at the
real tools instead of placeholder paths:

  Resume Maker       https://naukripatra.in/resume-builder/
  Photo Resizer      https://naukripatra.in/image-tools
  Signature Scanner  https://naukripatra.in/image-tools
  PDF Compressor     coming soon

- "Signature Maker" renamed to "Signature Scanner", with its
  description updated to match.
- Resume Maker now uses the document icon rather than the pen,
  so it no longer shares an icon with Signature Scanner.
- PDF Compressor has no URL yet, so it renders as a
  NON-CLICKABLE card with a "Coming soon" badge — a <span>,
  not a link. Nothing in this row can point at a 404.
- To switch PDF Compressor on later, put its URL in the `url`
  key of the $np_tools array in front-page.php. The card turns
  itself back into a link automatically; no other edit needed.

=====================================================
 VERSION 3.3 — STOP OLD CSS WINNING
=====================================================
Fixes the two reasons a site can keep showing the old look
after this theme is uploaded.

1. STYLESHEET NOW LOADS LAST
   The theme's CSS was enqueued at priority 20, which printed
   it BEFORE most plugin CSS. When two rules have the same
   specificity, the later stylesheet wins — so any plugin rule
   that matched as specifically as ours silently overrode the
   design. It is now enqueued at priority 999, so the theme's
   own styling comes after plugin styling, where it belongs.

   What this deliberately does NOT override: the Customizer's
   "Additional CSS" box, and anything a plugin injects straight
   into wp_head. Both print after every enqueued stylesheet, so
   they still win. That is correct — those are the site owner's
   own deliberate overrides and the theme should not fight them.
   If the old look is coming from there, empty that box.

2. CACHE-PROOF ASSET VERSIONS
   style.css and np-main.js were versioned by the theme version
   number, which only changes when the theme is re-versioned.
   A CDN, a caching plugin or a browser could therefore keep
   serving the PREVIOUS file after an upload, and the site went
   on looking like the old theme. Both files are now versioned
   by their own file modified time, so every upload produces a
   new URL and nothing can serve a stale copy.

IF IT STILL LOOKS OLD, CHECK IN THIS ORDER
  a. Purge the cache plugin (LiteSpeed / Autoptimize / WP
     Rocket), including its CSS/JS "combine" or "minify" cache,
     then purge Cloudflare if it is in front of the site.
  b. Appearance > Customize > Additional CSS — old NaukriPatra
     rules pasted there will beat the theme every time.
  c. Appearance > Customize > Colors / Typography — values set
     there are GeneratePress overrides, not theme defaults.
     Reset them so this theme's Design page controls the look.
  d. Hard-reload once (Ctrl+F5, or a private window) to rule out
     the browser's own copy.

=====================================================
 VERSION 3.4 — ONE MENU, AND THE MOBILE BAR FITS
=====================================================

1. TWO MENUS WERE SHOWING — FIXED
   The site displayed this theme's .np-navbar AND, stacked
   under it, GeneratePress' own header with its "MENU" toggle.
   Both were real; only the GP one responded, because the
   parent theme's script drives it.
   .np-navbar already carries the logo, the nav, search, the
   CTAs and the mobile drawer, so GP's navigation is redundant.
   It is now switched off with the generate_navigation_location
   filter, with a CSS fallback (.main-navigation, #mobile-header,
   .menu-toggle) for any GP setting that prints one anyway.
   Result: one header, one hamburger, and it works.

2. MOBILE BAR NO LONGER COLLIDES
   "NaukriPatra" was wrapping to two lines and "Download App"
   and "Post a Job" were printing on top of each other.
   - The brand is now white-space: nowrap and never wraps.
   - Below 900px, Download App lives in the drawer, not the bar.
   - Below 600px the bar holds only the hamburger, the brand and
     the search icon. Post a Job moved into the drawer, which
     now offers Download App / Post a Job / Login-Register.
   Verified with no overflow at 1280, 390, 360 and 320px.

3. UPLOADED LOGO IS USED
   The navbar shows the image from Customize > Site Identity
   when one is set, falling back to the NaukriPatra wordmark.
   The navbar is the site header now, so the logo belongs in it.

4. FLOATING "JOIN" BUTTON PINNED
   It was stretching across the screen. It now sets left:auto
   and max-width:max-content, so no other stylesheet can pull it
   out of its pill shape at the right edge.

-----------------------------------------------------
 STILL SEEING BLUE? THE OLD STYLESHEET IS STILL LOADING
-----------------------------------------------------
v2.8 and v3.x share 50 class names — .np-btn, .np-logo,
.np-hero, .np-float, .np-card and more. If any copy of the OLD
style.css is still being served, it repaints all of them: the
wordmark goes blue, buttons go blue gradient, and the floating
Join button jumps out of place. That is exactly what a blue
logo on a navy bar means.

This theme CANNOT out-rank it from inside style.css, because
the old copy loads later. Remove the source:
  a. Purge the cache plugin (LiteSpeed / Autoptimize / WP
     Rocket) INCLUDING its combined/minified CSS and JS cache,
     then purge Cloudflare. A stale combined bundle is the most
     common cause and also explains a dead hamburger, since the
     bundle carries the old JS too.
  b. Appearance > Customize > Additional CSS — if the old theme
     CSS was ever pasted there, delete it. It prints after every
     enqueued stylesheet and always wins.
  c. Check no second copy of the old theme folder is left in
     wp-content/themes.

=====================================================
 VERSION 3.5 — SECTION LISTS RESTORED
=====================================================

WHAT WAS LOST AND IS NOW BACK
v2.8's homepage ended with one card per section, each listing
that section's 6 newest posts with a NEW badge, the last date
and a "View All" link. That is how Result, Admit Card, Answer
Key, Syllabus and Admission were surfaced on the front page.

v3.0 replaced that block with icon-only category tiles showing
just a name and a post count, and dropped the lists entirely.
That was a regression, not an intentional trade — the tiles
tell a visitor a section exists but never show what is in it.

v3.5 brings the lists back, restyled to the new design:
- One card per section, its 6 newest posts, NEW badge, last
  date in alert red, and a "View all" link to the category.
- Sits directly below "Latest jobs", above the app band.
- 3 columns on desktop, 2 on tablet, 1 on phone.
- Latest Jobs is intentionally NOT repeated here, because the
  full "Latest jobs" list sits directly above it. To include
  it anyway, delete the `unset( $np_sec_lists['Latest Jobs'] );`
  line in front-page.php and all 6 cards render.
- The icon-only tiles stay where they are, higher up the page,
  as quick navigation.

NOTE ON THE TICKER
The live ticker was never removed. np_render_ticker() is
unchanged in inc/features.php and is still called on the
homepage, directly under the hero. If it is not appearing on
the live site, that is the leftover v2.8 stylesheet again:
.np-ticker, .np-ticker-label, .np-ticker-track and
.np-ticker-move are four of the 50 class names the two
stylesheets share. See the v3.4 notes above for how to clear it.

=====================================================
 VERSION 3.6 — SMALLER HERO, SECOND (RESULT) TICKER
=====================================================

1. HERO PANEL IS MUCH SMALLER
   It was filling an entire phone screen, so nothing below it
   was visible without scrolling. Every element is still there
   — eyebrow, headline, subtext, search, the
   All/Government/Private toggle and the three stats. They are
   just packed tighter:
     phone    776px -> 455px
     desktop  512px -> 439px
   What changed:
   - Default H1 size 42 -> 34px, mobile H1 27 -> 21px.
   - Padding, margins and the stat block all tightened.
   - The search bar stays on ONE row on a phone instead of
     wrapping its button onto a second.
   - The forced line break in the headline is desktop-only now;
     on a phone the headline flows and saves a whole line.

   NOTE: if you have already pressed Save on Appearance >
   NaukriPatra Design, your saved sizes win over these new
   defaults. Set "H1 / hero headline" to 34 and "H1 on mobile"
   to 21 there, or press "Reset to defaults".

2. GENERATEPRESS HEADER BAR REMOVED
   v3.4 hid GP's navigation but left its header bar, which was
   still printing a second branding band above .np-navbar — the
   blue strip with a duplicate logo. .np-navbar carries the logo,
   nav, search and CTAs, so the GP bar is now hidden outright.
   That also gives back about 60px above the fold.

   RESULT: on a 390x727 phone the hero, BOTH tickers and the
   "Browse by category" heading are visible with no scrolling.

3. SECOND TICKER — LIVE RESULT
   The homepage now runs two tickers, as the site did before:
     LIVE         newest listings of any kind, red label
     LIVE RESULT  Result category only, green label, trophy icon
   np_render_ticker() now takes optional arguments —
   label, category, count, class, icon. Called with NO arguments
   it behaves exactly as it always has (8 newest posts, red LIVE
   label), so every existing call site keeps working untouched.
   A ticker whose category is empty prints nothing at all, so
   neither bar can ever appear blank.

   To add a third ticker, call it again in front-page.php, e.g.
     np_render_ticker( array(
       'label' => 'LIVE ADMIT CARD', 'category' => 'admit-card',
       'class' => 'np-ticker-result', 'icon' => 'card' ) );

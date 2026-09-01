# KATAPALI +3 COLLEGE — WordPress Theme

A complete, colourful government-college WordPress theme built for **KATAPALI +3 COLLEGE, KATAPALI**. It ships with custom post types for every dynamic section of the site, a Theme Customizer for college info/branding, and a one-click **Demo Content Importer** that fills the whole site with realistic content and images — nothing is empty. Everything is then editable from `wp-admin`, no coding required.

## What's inside the zip

```
katapali-college/            <- upload this folder as your WordPress theme
  style.css                  <- theme header + WordPress requires this file
  functions.php               <- theme setup, asset loading, SVG upload support
  header.php / footer.php
  front-page.php              <- homepage (hero, principal, faculty, stats, notices, gallery, map)
  page.php                    <- About/Academics/Admissions/etc. content pages
  archive-kc_*.php / single-kc_*.php   <- Notices, Recruitment, Tenders, Faculty, Gallery, Downloads
  inc/
    cpt.php                   <- registers the custom post types & taxonomies
    metaboxes.php              <- the custom fields you fill in for each post
    customizer.php              <- college info / colours / social / map / principal / stats
    template-tags.php           <- shared card/render helpers
    nav-walker.php              <- menu markup
    demo-importer.php           <- the "fill my site with demo content" button
  assets/
    css/style.css               <- all site styling (colourful blue + gold government-college theme)
    js/theme.js                  <- hero slider, faculty carousel, filters, lightbox, mobile nav
    demo-images/                 <- 65 bundled demo photos/banners + demo PDFs, used by the importer
```

## Installation (5 minutes)

1. **Log in to your WordPress site** as an administrator.
2. Go to **Appearance → Themes → Add New → Upload Theme**.
3. Choose `katapali-college.zip` (the zip containing the `katapali-college` folder) and click **Install Now**, then **Activate**.
4. A new **Katapali College** menu appears in the left sidebar. Click **Demo Content Importer** under it.
5. Click **Import Demo Content Now**. This creates, in one step:
   - 4 homepage hero slides
   - 20 faculty members (7 marked to show on the homepage slider)
   - 6 notices, 3 recruitment postings, 2 tenders
   - 16 gallery photos (8 featured on the homepage) across 4 categories
   - 10 downloadable documents (demo PDFs — replace with your real files)
   - 21 external links across the footer's Resources and Useful Links columns (RTI, SAMS, UGC, NAAC, SWAYAM, National Scholarship Portal, etc.)
   - 8 fully-written content pages (About Us, Academics, Admissions, Examination, Student Corner, Alumni, Downloads, Contact Us)
   - A complete navigation menu with submenus, assigned automatically
   - The college logo, set as the site logo
6. Visit your homepage — the site is now fully populated and ready to show a client.

No database import, no XML file, no plugin dependency beyond WordPress core — the importer uses your bundled theme images directly.

## Editing content (what your client will actually use)

Everything on the public site is a normal WordPress post, page, or Customizer setting:

| What you see on the site | Where to edit it |
|---|---|
| Notices, Recruitment, Tenders, Faculty, Gallery photos, Downloads, Hero Slides, Links (Quick Links / Resources / Useful Links) | **Katapali College** menu in wp-admin (each is Add/Edit/Delete/Search like Posts) |
| College name, address, phone, email, established year, social links (incl. WhatsApp) | **Appearance → Customize → College Info / Social Media Links** |
| Header logo size (40-160px) | **Appearance → Customize → Header Logo Size** |
| Primary/Secondary/Accent/Dark/Gold theme colours | **Appearance → Customize → Theme Colours** (live preview) |
| Logo | **Appearance → Customize → Site Identity** |
| Principal's photo, name, message | **Appearance → Customize → Principal's Message** |
| Homepage stat numbers (students/faculty/departments/years) | **Appearance → Customize → Quick Stats** |
| Google Map embed & directions note (used on the homepage and the footer's "Find Us on Map" column) | **Appearance → Customize → Google Map** |
| About Us / Academics / Admissions / Examination / Student Corner / Alumni / Contact Us page text | **Pages** in wp-admin — edit like any normal WordPress page (raw HTML is supported; use the Code Editor / Custom HTML block for tables) |
| Menus & submenus | **Appearance → Menus** |

Each custom post type (Notice, Recruitment, Tender, Faculty, Gallery, Download, Slide, Link) has its own **Details** box on the edit screen with the exact fields that post needs (dates, salary, department, file links, etc.) — fill them in, set a Featured Image where relevant, and Publish.

The header has a fixed 3-part layout: a thin contact/social bar, then a centred header (logo on the left, college name and address centred, Admin Login on the right), then a dedicated colour-bar menu row. The homepage below it follows a fixed order: hero slider, a scrolling "Latest Notice" ticker, a compact About + Principal's Message pair of cards, a three-column Notices/Recruitment/Tenders block, the faculty carousel, the student/faculty/department/years stats bar, and the photo gallery — with the footer split into Reach Us At / Resources / Useful Links / Find Us on Map columns. Every one of those pieces is a normal editable post type, page, or Customizer field, and every section keeps a fixed column layout (equal-height cards) so nothing reflows or "spreads" between page loads.

## Reusing this theme for a different college

1. Update the college name, address, colours, logo, etc. in the Customizer.
2. Either edit each imported demo item to your college's real content, **or** delete all demo content (Posts → select all → Move to Trash → Empty Trash, for each post type) and add your own — the theme works identically either way.
3. Re-running **Demo Content Importer** is always safe if you want a fresh demo set again — it does not touch your Customizer settings or existing pages with the same slugs.

## Demo admin login

Use the WordPress account you created during installation (or the one your host set up) — this theme adds an **Admin Login** button in the header that links straight to `wp-login.php`. There is no separate custom admin panel; wp-admin *is* the admin panel, which is more secure and far more capable than a custom one.

## Requirements

- WordPress 6.0 or later
- PHP 7.4 or later
- No required plugins. The theme allows `.svg` uploads (needed for the bundled demo artwork) — if your host's media library still rejects SVG files, ask your host to enable the `image/svg+xml` mime type, or simply replace the demo images with your own JPG/PNG photos through the Featured Image box.

## Tested

This theme was installed and smoke-tested end-to-end on a real WordPress installation (WordPress 7.1, PHP 8.4) before packaging: theme activation, the Demo Content Importer, every page/archive/single template, image and PDF attachments, and the generated navigation menu were all verified to load with zero PHP errors.

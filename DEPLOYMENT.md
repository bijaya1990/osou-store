# NaukriPatra Result System — Deployment & Handoff Guide

Everything you need to install this on normal cPanel shared hosting. Written
for a beginner: follow it top to bottom.

Throughout this guide, replace `naukripatra.in` with your own domain if it
differs.

---

## 1. What is in the package

```
naukripatra-result-system/
├── DEPLOYMENT.md                       ← this guide
│
├── result/                             ← upload this to public_html/
│   ├── index.php                       front controller (listing, result pages, ticker feed)
│   ├── install.php                     one-time installer — DELETE after installing
│   ├── config.sample.php               reference copy of the settings file
│   ├── schema.sql                      MySQL table definitions
│   ├── ticker-widget.php               ticker for non-WordPress themes (optional)
│   ├── README.md                       technical documentation
│   ├── .htaccess                       pretty URLs + file protection
│   │
│   ├── admin/
│   │   ├── index.php                   redirects to dashboard or login
│   │   ├── login.php
│   │   ├── logout.php
│   │   ├── dashboard.php               counters + list of results
│   │   ├── add-result.php
│   │   ├── edit-result.php             edit, publish/unpublish, ticker toggle
│   │   ├── import.php                  upload → map columns → validate → import
│   │   ├── students.php                imported student records
│   │   ├── delete-result.php
│   │   ├── account.php                 change your password
│   │   └── partials/
│   │       ├── head.php
│   │       ├── foot.php
│   │       └── result-fields.php       shared result form fields
│   │
│   ├── includes/                       (protected — never opened directly)
│   │   ├── .htaccess
│   │   ├── bootstrap.php
│   │   ├── database.php
│   │   ├── auth.php
│   │   ├── security.php
│   │   ├── functions.php
│   │   ├── spreadsheet.php             CSV + XLSX reader
│   │   ├── importer.php                column mapping, validation, import
│   │   └── result-form.php
│   │
│   ├── public/
│   │   ├── home.php                    list of published results
│   │   ├── result.php                  roll-number search page
│   │   ├── marksheet.php               the professional result display
│   │   ├── external-redirect.php
│   │   ├── not-found.php
│   │   ├── layout-top.php
│   │   ├── layout-bottom.php
│   │   ├── ticker-feed.php             JSON feed at /result/ticker.json
│   │   └── assets/
│   │       ├── .htaccess
│   │       ├── css/
│   │       │   ├── result.css          public pages + print styles
│   │       │   ├── admin.css           admin panel
│   │       │   └── ticker.css          homepage ticker
│   │       └── js/
│   │           ├── result.js           print / save as PDF
│   │           ├── admin.js            form helpers
│   │           └── ticker.js           smooth seamless scrolling
│   │
│   ├── tools/
│   │   └── reset-password.php          locked emergency password reset
│   │
│   └── uploads/                        must be writable
│       ├── .htaccess                   blocks script execution
│       ├── logos/                      institution logos
│       └── tmp/                        staged import files (auto-cleaned)
│           └── .htaccess
│
└── wordpress-plugin/
    └── naukripatra-result-ticker/      ← install this in WordPress
        ├── naukripatra-result-ticker.php
        └── readme.txt
```

**Not included, on purpose:** `config.php` (created during installation, holds
your database password), any test or sample results, any student data, any
credentials or API keys, and all development/test files.

The package contains **0 results and 0 students**. Until you publish your first
result, the homepage ticker shows `🔴 LIVE RESULTS → Coming Soon`.

---

## 2. Shared hosting installation (cPanel)

### Step 1 — Upload the `result/` folder

1. Log in to cPanel → **File Manager**.
2. Open `public_html` (this is your website's root, where WordPress lives).
3. Click **Upload** and upload the ZIP file.
4. Back in File Manager, right-click the ZIP → **Extract**.
5. Move the `result` folder so it sits **directly inside `public_html`**:

```
public_html/
├── wp-admin/          ← your existing WordPress
├── wp-content/
├── wp-includes/
├── index.php
└── result/            ← the result system
```

`wordpress-plugin/` does **not** go in `public_html`. Keep it on your computer;
you will upload it through the WordPress dashboard in section 6.

### Step 2 — Set folder permissions

In File Manager, right-click → **Change Permissions**:

| Folder | Permission |
| --- | --- |
| `result/uploads` | `755` (use `775` if the installer says it is not writable) |
| `result/uploads/logos` | same as above |
| `result/uploads/tmp` | same as above |
| `result/` itself | `755` — needed once so the installer can create `config.php` |

Everything else can stay at the default (`644` for files, `755` for folders).

### Step 3 — Create the MySQL database

cPanel → **MySQL® Databases**.

1. Under *Create New Database*, type a name, e.g. `results`.
   cPanel prefixes it automatically, so the real name becomes something like
   `naukri_results`. **Write the full name down.**
2. Click **Create Database**.

### Step 4 — Create the MySQL user

On the same page, under *MySQL Users → Add New User*:

1. Username, e.g. `resultuser` → becomes `naukri_resultuser`.
2. Click **Password Generator**, generate a strong password, tick "I have
   copied this password", and save it somewhere safe.
   **Never share this password with anyone, including me.**
3. Click **Create User**.

### Step 5 — Assign the user to the database

Still on the same page, under *Add User To Database*:

1. Select your user and your database → **Add**.
2. On the privileges screen tick **ALL PRIVILEGES** → **Make Changes**.

You now have three values: database name, database user, database password.

> You may also reuse your existing WordPress database. The result system uses
> its own `np_res_` table prefix, so it cannot collide with WordPress tables.
> A separate database is still cleaner.

### Step 6 — Run the installer

Open in your browser:

```
https://naukripatra.in/result/install.php
```

You will see a **Server check** table first (PHP version, PDO MySQL, zip, XML,
writable folders). Green "OK" everywhere means you are good to go.

### Step 7 — What to enter

| Field | What to type |
| --- | --- |
| Database host | `localhost` (correct on almost all cPanel hosts) |
| Database name | the full name from Step 3, e.g. `naukri_results` |
| Database user | the full user from Step 4, e.g. `naukri_resultuser` |
| Database password | the password you generated in Step 4 |
| Table prefix | leave as `np_res_` |
| Result system URL | `https://naukripatra.in/result` (no trailing slash) |
| Site name | e.g. `NaukriPatra Results` |
| Admin username | pick your own, e.g. `npadmin` (not "admin") |
| Admin email | optional |
| Password / Repeat | your own strong password, **at least 10 characters** |

Click **Install**.

### Step 8 — What the installer does

1. Connects to your database with the credentials you typed.
2. Creates the five tables from `schema.sql`:
   `np_res_results`, `np_res_result_students`, `np_res_admins`,
   `np_res_import_logs`, `np_res_login_attempts`.
3. Creates your administrator account, storing the password as a bcrypt hash —
   the password itself is never saved anywhere in readable form.
4. Writes `result/config.php` with your settings.
   If the folder is not writable, it shows the file contents on screen and asks
   you to create `config.php` yourself with File Manager and paste them in.

### Step 9 — Test the installation

1. Open `https://naukripatra.in/result/` — you should see
   "Online Result Portal" and "No results have been published yet."
2. Open `https://naukripatra.in/result/admin/` and sign in.
3. The dashboard should show **Total Results 0, Published 0, Draft 0, Total
   Students 0**.

### Step 10 — Delete `install.php`

**Do this immediately after a successful install.**

File Manager → `public_html/result/` → right-click `install.php` → **Delete**.

Leaving it on the server is a security risk. Re-opening it later will refuse to
run while an admin account exists, but delete it anyway.

---

## 3. Configuration

* **The database configuration file is `result/config.php`.**
* **You do not need to rename anything.** `install.php` creates `config.php`
  automatically. `config.sample.php` is only a reference copy — leave it alone.
* Values inside `config.php`: database host/name/user/password, table prefix,
  the public URL, upload size limits, session settings, site name.
* You would only ever edit it by hand if you change hosting, change the
  database password, or move the site to a new domain.
* Folders needing write permission: `result/uploads/`, `result/uploads/logos/`,
  `result/uploads/tmp/`, plus `result/` itself during installation only.

Your database password lives only in `config.php` on your own server. It is not
in the ZIP, not in the code, and I never need to see it.

---

## 4. Admin login and passwords

| Purpose | URL |
| --- | --- |
| Admin login | `https://naukripatra.in/result/admin/` |

* **First account:** created by you during installation (Step 7). There is no
  default username and no default password shipped with the system.
* **Change your password:** sign in → click your username in the top-right →
  *Change Password*. Direct URL: `/result/admin/account.php`.
* **Forgotten password:**
  1. cPanel File Manager → open `public_html/result/`.
  2. **+ File** → create an empty file named `reset-allowed.txt`.
  3. Open `https://naukripatra.in/result/tools/reset-password.php`.
  4. Enter your admin username and a new password.
  5. **Delete `reset-allowed.txt`.**

  Without that file the reset tool refuses to run, so nobody who merely finds
  the URL can take over your account.

* Login protection: after 8 failed attempts from one IP address, logins are
  blocked for 15 minutes. Admin sessions expire after 1 hour of inactivity.

---

## 5. WordPress plugin installation

Your existing theme and homepage are **not** modified by this.

1. On your computer, create a ZIP of the folder
   `wordpress-plugin/naukripatra-result-ticker/`
   (the ZIP must contain the folder `naukripatra-result-ticker`, with
   `naukripatra-result-ticker.php` inside it).
2. WordPress Dashboard → **Plugins** → **Add New** → **Upload Plugin**.
3. Choose the ZIP → **Install Now** → **Activate Plugin**.
4. Go to **Settings → Live Results Ticker**.
5. Check these settings:

| Setting | Value |
| --- | --- |
| Path to result config.php | `/home/YOURCPANELUSER/public_html/result/config.php` |
| Result system URL | `https://naukripatra.in/result` |
| Ticker label | `LIVE RESULTS` |
| Text when nothing is published | `Coming Soon` |
| Button text | `CHECK RESULT` |
| Maximum results shown | `10` |
| Cache (seconds) | `300` — leave it there; publishing now clears the cache automatically |

The path is usually pre-filled correctly. If you are unsure of your exact
server path, cPanel → File Manager shows it at the top, or check
**Tools → Site Health → Info → Directories** in WordPress.

6. Press **Save Changes**. A green box at the top confirms:
   *"Connected to the result system. 0 published result(s)."*
   A red box means the path or the database is wrong — fix it before continuing.

---

## 5a. How the ticker stays up to date (v1.1.0+)

You never need to clear a cache by hand after publishing. Three mechanisms work
together:

1. **Instant invalidation.** Publishing, unpublishing, editing, toggling the
   ticker setting or deleting a result rewrites
   `result/uploads/.ticker-revision`. The plugin includes that file's timestamp
   in its cache key, so the very next page view fetches fresh data.
2. **Short fallback lifetimes.** A ticker showing "Coming Soon" is cached for
   only 30 seconds, and a database error for 15 seconds — so the empty state
   can never stick, even if the stamp file cannot be written. A ticker that is
   showing results keeps the full 5-minute cache, so performance is unchanged.
3. **Page-cache bypass.** If a caching plugin (LiteSpeed, WP Rocket, W3 Total
   Cache, Cloudflare…) serves a saved copy of your homepage, the saved HTML
   would still say "Coming Soon". The ticker therefore re-reads
   `/result/ticker.json` in the browser — that address is served by the result
   system, not WordPress, so no WordPress or CDN page cache applies — and
   updates itself in place if the saved HTML is out of date.

Settings → Live Results Ticker shows when the result system last signalled a
change, and has a **Clear ticker cache now** button for the rare case you want
to force it.

If the ticker is ever stuck, check in this order:

* Does `result/uploads/` exist and is it writable? (The settings page warns if
  the stamp file is missing.)
* Does `https://naukripatra.in/result/ticker.json` list your result? If not,
  the result is not Published, or "Show on Homepage Ticker" is set to No.
* Is the result system URL in the plugin settings correct? The browser refresh
  uses it to reach the feed.

## 6. Adding the ticker to your existing homepage

The exact shortcode is:

```
[naukripatra_results_ticker]
```

Pick **one** of these methods. None of them change your theme's design.

**Method A — homepage built with a page builder or the block editor (easiest)**

Pages → open your homepage → add a **Shortcode block** (or a Text/HTML widget)
at the very top of the content → paste `[naukripatra_results_ticker]` →
**Update**.

**Method B — your homepage uses `front-page.php` or `home.php`**

Use a **child theme** (never edit the parent theme directly — an update would
wipe it). Open `front-page.php` and add this line where you want the ticker,
usually right after the header:

```php
<?php echo do_shortcode('[naukripatra_results_ticker]'); ?>
```

Yes — `echo do_shortcode('[naukripatra_results_ticker]');` is the correct
method for a PHP template. Add only that one line; change nothing else.

**Method C — no template editing at all**

Appearance → Widgets (or Customize) → add a **Text** or **Shortcode** widget to
a header/above-content widget area → paste the shortcode.

Optional attributes:

```
[naukripatra_results_ticker label="LIVE RESULTS" empty_text="Coming Soon" button_text="CHECK RESULT" limit="10"]
```

The ticker scrolls right to left, pauses on hover on desktop, is fully
responsive, and shows only results that are **Published** *and* have
**Show on Homepage Ticker = Yes**, newest first.

---

## 7. Your URLs after installation

| Page | URL |
| --- | --- |
| Result portal home (all published results) | `https://naukripatra.in/result/` |
| Admin panel | `https://naukripatra.in/result/admin/` |
| Admin login | `https://naukripatra.in/result/admin/login.php` |
| Change password | `https://naukripatra.in/result/admin/account.php` |
| Installer (delete after use) | `https://naukripatra.in/result/install.php` |
| One result (internal) | `https://naukripatra.in/result/<result-slug>/` |
| One result (external link) | same URL — redirects to the official website |
| Ticker JSON feed | `https://naukripatra.in/result/ticker.json` |
| Password reset (locked) | `https://naukripatra.in/result/tools/reset-password.php` |

The slug is set on the result form; if you leave it blank it is generated from
the institution and title, e.g.
`https://naukripatra.in/result/abc-college-annual-examination-result-2026/`.

If pretty URLs do not work (rare — needs Apache `mod_rewrite`), the fallback
`https://naukripatra.in/result/index.php?slug=abc-college-...` always works.

---

## 8. First test — internal result

### Create the test Excel/CSV file

Open Excel or Google Sheets and type exactly this, then save as **CSV** or
**.xlsx** (`test-result.csv`):

| Roll Number | Registration Number | Student Name | English | Odia | History | Total | Division | Result |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 100001 | REG100001 | Test Student One | 78 | 82 | 75 | 235 | FIRST | PASS |
| 100002 | REG100002 | Test Student Two | 55 | 61 | 49 | 165 | SECOND | PASS |
| 100003 | REG100003 | Test Student Three | 30 | 28 | 25 | 83 |  | FAIL |

Use obviously fake names and roll numbers so the test data is easy to spot.

### Run the workflow

1. Sign in → **+ Add New Result**.
2. Result Type: **Internal Result**.
3. Institution Name: `TEST COLLEGE (DELETE ME)`.
   Examination Name: `Test Examination 2026`.
   Class/Course: `+3 1st Year`. Session: `2025-26`. Result Date: today.
4. Status: **Draft**. Show on ticker: **Yes**. → **Save Result**.
   (Internal results cannot be published before data is imported — this is
   deliberate, so an empty result page can never go live.)
5. You land on the import screen. Choose your file → **Upload & Continue**.
6. **Map columns.** The system pre-fills its best guess. Check that
   *Roll Number → Examination Roll Number*, *Student Name → Student Name*, and
   English/Odia/History are *Subject — Secured Marks* with the subject name
   filled in. Type `100` in the "Max marks" box for each subject.
7. Click **Check File**. The validation report should read: total rows 3,
   valid rows 3, invalid rows 0, duplicates 0.
8. Click **Import 3 Student Record(s)**. You are taken to the student list.
9. Go back to the result → **Publish Result**.
10. Open your WordPress homepage → the ticker now shows
    `🔴 LIVE RESULTS → Test Examination... → CHECK RESULT`
    (if the cache is set to 300 seconds, wait up to 5 minutes or set it to 0).
11. Click it → the result page opens → enter `100001` → **View Result**.
12. You should see the full marksheet: institution header, candidate
    information, subject-wise marks with maximum and secured columns, total,
    percentage, division, and a green **PASS** badge.
13. Click **Print Result** and check the print preview: no menus or buttons,
    just a clean marksheet.
14. Also test a wrong roll number, e.g. `999999` → *"Result Not Found —
    Please check your Examination Roll Number and try again."*

### Delete the test data

Admin → Dashboard → click the test result → **Delete** →
type `DELETE` → **Delete Permanently**.

This removes the result **and all its student records and import history** in
one step. Confirm the dashboard shows 0 / 0 / 0 / 0 again.

---

## 9. Second test — external result link

1. **+ Add New Result** → Result Type: **External Result Link**.
2. External Result URL: any safe real site, e.g. `https://www.example.com`
   (or a genuine board site such as `https://bseodisha.ac.in`).
   Button Text: `CHECK RESULT`.
3. Institution Name: `TEST EXTERNAL (DELETE ME)`.
   Examination Name: `Test External Result 2026`. Result Date: today.
4. Status: **Published**. Show on ticker: **Yes** → **Save Result**.
   (No Excel file is requested, and no student records are created.)
5. Check the homepage ticker — the entry appears next to any internal ones.
6. Click **CHECK RESULT** → it opens the external website in a new tab.
7. Also open `https://naukripatra.in/result/<that-slug>/` directly → it
   redirects to the same external website.
8. Delete it: Dashboard → the test result → **Delete** → type `DELETE`.

Only `http://` and `https://` links are accepted. `javascript:`, `data:`,
`file:` and malformed addresses are rejected when saving and again before any
link is displayed.

---

## 10. Recommended Excel format (internal results)

```
Roll Number | Registration Number | Student Name | English | Odia | History | Maximum Marks | Secured Marks | Total | Percentage | Division | Result
```

**Only the roll-number column is required.** Everything else is optional.

**Your subject columns can be anything.** Because the importer has a column
mapping screen, you tell the system what each column means every time you
import — so Physics/Chemistry/Maths, paper codes, semester subjects or a
completely different set works equally well, and different results can have
completely different subjects. Nothing is hard-coded.

Rules to follow:

* Row 1 must be the column headings. Every row below it is one student.
* One roll number may appear only once per file.
* Marks may be numbers, or markers such as `AB` — non-numeric values are shown
  on the marksheet exactly as you typed them.
* If you have per-subject maximum marks in their own columns (`English Max`,
  `Odia Max`, …), map them as *Subject — Maximum Marks* using the **same
  subject name** as the marks column. Otherwise just type the maximum once on
  the mapping screen.
* Totals and percentage are calculated **only** if you leave those checkboxes
  ticked and your file has no such column.
* **Division is never calculated.** It appears only if your file provides it.
* Extra columns (centre, stream, remarks) can be mapped as *Extra detail* and
  are displayed in the candidate information box.

---

## 11. Limitations (please read)

**File formats**

* `.xlsx` (Excel 2007 and newer) — supported. Reads the **first worksheet
  only**; formulas are read as their last saved value.
* `.csv` — supported, with comma/semicolon/tab/pipe delimiters auto-detected.
* `.xls` (old Excel 97–2003 binary) — **not supported.** Open it in Excel and
  *Save As* `.xlsx` or CSV first.
* `.ods`, Google Sheets links, PDF — not supported. Export to CSV/XLSX.

**Sizes**

* Maximum upload: 8 MB (also limited by your host's `upload_max_filesize` and
  `post_max_size` — typically 8–64 MB on shared hosting).
* Maximum rows per import: 20,000. 150 students is trivial for this.
* Institution logo: 1 MB, JPG/PNG/GIF/WEBP only.

**Server requirements**

* PHP 7.0+ (tested on PHP 8.x). PHP 7.4+ recommended.
* MySQL 5.6+ / MariaDB 10.x, with InnoDB and `utf8mb4`.
* Required PHP extensions: `pdo_mysql`, `json`, `session`.
* Needed for `.xlsx` import only: `zip` and `xml`/`XMLReader`. Without them
  CSV import still works and the admin screen says so.
* Recommended: `mbstring` or `iconv` (handles non-English names correctly),
  `fileinfo` (upload MIME checking), `gd` or `exif` not required.

**Web server**

* Apache with `.htaccess` enabled (the norm on cPanel) gives pretty URLs plus
  the protection rules for `config.php`, `includes/` and `uploads/`.
* Without `mod_rewrite`, pretty URLs stop working but
  `/result/index.php?slug=...` still does.
* **On nginx, `.htaccess` is ignored** and you must add these rules yourself,
  otherwise `config.php` and uploaded files are exposed:

  ```nginx
  location ~ ^/result/(includes|uploads)/ { deny all; }
  location ~ ^/result/(config\.php|schema\.sql)$ { deny all; }
  location ~ ^/result/([A-Za-z0-9._-]+)/?$ { try_files $uri $uri/ /result/index.php?npr_route=$1; }
  ```

**Security notes**

* The ticker's self-refresh needs the result system and WordPress on the same
  domain, or the feed reachable from the browser; it is served with
  `Access-Control-Allow-Origin: *` so a subdomain works too. Without
  JavaScript the ticker still renders server-side (just subject to your page
  cache).
* Serve the site over **HTTPS** — admin passwords are typed into these forms.
* **Delete `install.php`** after installing.
* Results are public by design: anyone who knows a roll number can view that
  marksheet. Lookups are rate-limited (40 per 10 minutes per visitor) to stop
  bulk scraping, but do not treat results as confidential data.
* Keep `config.php` at permission `640` or `644`, never `777`.
* The system does not send email (no "forgot password" email); resets are done
  through the file-manager method in section 4.

**Functional limits**

* Single admin panel — no separate roles or per-institution logins.
* No student-side login, no SMS/email notification, no PDF generation on the
  server (printing uses the browser's own "Save as PDF", which is what Indian
  result portals typically do).
* Editing an individual student's marks is not possible from the admin screens;
  correct the spreadsheet and re-import (choose *Update the existing record*).
* No multi-language interface (English only).

---

## 12. Backup procedure

**Before every big change, and after each result you publish.**

**A. Database backup (most important)**

cPanel → **Backup** → *Download a MySQL Database Backup* → click your database
→ a `.sql.gz` file downloads. Keep it somewhere safe.

Alternative: phpMyAdmin → select your database → **Export** → *Quick* → **Go**.

**B. Files backup**

File Manager → select the `result` folder → **Compress** → download the ZIP.
This includes `config.php` and everything in `uploads/`.

**C. What matters inside those**

* `result/config.php` — your settings and database password. Irreplaceable.
* `result/uploads/logos/` — institution logos.
* `result/uploads/tmp/` — nothing to keep; staged import files are deleted
  automatically after six hours.
* Keep your original Excel/CSV files on your own computer. They are the
  authoritative source and are not stored on the server after import.

**D. Restoring**

1. Re-upload the `result` folder (or extract your files ZIP) into
   `public_html/`.
2. phpMyAdmin → select the database → **Import** → choose your `.sql` backup →
   **Go**. (If the tables already exist, drop them first.)
3. Make sure `result/config.php` is present and has the correct database
   details.
4. Open `/result/admin/` and sign in. Do **not** run `install.php` again.

---

## 13. Production checklist

```
[ ] Upload result/ into public_html/
[ ] Set uploads/ (and logos/, tmp/) to 755 or 775
[ ] Create MySQL database in cPanel
[ ] Create MySQL user with a strong password
[ ] Assign the user to the database with ALL PRIVILEGES
[ ] Open /result/install.php and complete the form
[ ] Confirm admin account was created and you can sign in
[ ] Confirm tables exist (np_res_results, np_res_result_students, ...)
[ ] DELETE install.php from the server
[ ] Zip and install the WordPress ticker plugin
[ ] Configure Settings → Live Results Ticker (green "Connected" message)
[ ] Add [naukripatra_results_ticker] to the homepage
[ ] Test an internal result end to end (create → import → publish → check)
[ ] Test an external result link
[ ] Delete BOTH test results (type DELETE to confirm)
[ ] Dashboard shows 0 results, 0 published, 0 draft, 0 students
[ ] Homepage ticker shows: 🔴 LIVE RESULTS → Coming Soon
[ ] Back up the database and the result/ folder
[ ] Confirm the site is served over HTTPS
```

---

## 14. Final summary

**What you upload**

* `result/` → into `public_html/` on your server.
* `wordpress-plugin/naukripatra-result-ticker/` → zipped, through WordPress →
  Plugins → Add New → Upload Plugin.

**What you edit**

* Nothing, in normal use. The installer writes `result/config.php` for you, and
  everything else is done through the admin panel.
* One optional line if you use Method B for the homepage:
  `<?php echo do_shortcode('[naukripatra_results_ticker]'); ?>` in your **child
  theme's** `front-page.php`.
* `result/config.php` only if your database password or domain ever changes.

**What you must NOT edit**

* Anything in `result/includes/` — the engine.
* `result/admin/*`, `result/public/*`, `result/index.php`, `schema.sql`.
* `result/public/assets/*` — CSS/JS (changes are lost if you ever update).
* The plugin PHP file — use its settings page instead.
* Your parent WordPress theme — always use a child theme.

**What you do in cPanel**

1. Upload and extract `result/` into `public_html/`.
2. Set `uploads/` permissions.
3. Create the database, the user, and assign the user to the database.
4. Delete `install.php` after installing.
5. Take backups.

**What you do in WordPress**

1. Install and activate the ticker plugin.
2. Settings → Live Results Ticker → set the config path and result URL.
3. Add `[naukripatra_results_ticker]` to your homepage.

**What you do in phpMyAdmin**

* Normally nothing — the installer creates all tables.
* Only for backups (Export) and restores (Import), or to confirm the
  `np_res_*` tables exist.

**Everyday workflow from now on**

```
Login → Add New Result → Save → Upload Excel/CSV → Map columns
      → Check File → Import → Publish → the ticker updates itself
```

No PHP editing, ever.

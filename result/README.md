# NaukriPatra — Result Management System

A standalone PHP + MySQL result portal that sits next to an existing WordPress
site. Publish school, college, university, board, semester or annual results by
uploading an Excel/CSV file — no PHP editing required, ever.

Two kinds of result are supported:

| Type | Use when | What students get |
| --- | --- | --- |
| **Internal Result** | You have the student-wise Excel/CSV data | Roll-number search on your own site and a full professional marksheet |
| **External Result Link** | The result is already published on an official board/university/school website | The ticker entry links straight to that website; no student data is stored |

Both types appear together in the homepage Live Results ticker.

---

## 1. Requirements

* PHP 7.0 or newer (works on PHP 8.x)
* MySQL 5.6+ / MariaDB
* PDO MySQL extension
* `zip` + `xml` extensions for `.xlsx` import (CSV import works without them)
* Ordinary shared hosting — no Node.js, Composer, framework or paid service

## 2. Installation

1. Create a MySQL database (you may reuse the WordPress database — the tables
   use their own `np_res_` prefix).
2. Upload the `result/` folder to your web root so it is reachable at
   `https://your-site.com/result/`.
3. Make `result/uploads/` writable (`chmod 755`, or `775` on some hosts).
4. Open `https://your-site.com/result/install.php` in a browser.
5. Fill in the database details, the public URL and the administrator account
   you want (password must be at least 10 characters).
6. Press **Install**. The tables are created, `config.php` is written and your
   admin account is ready. If `config.php` cannot be written automatically, the
   installer shows its contents for you to paste into the file yourself.
7. **Delete `install.php` from the server.**
8. Sign in at `https://your-site.com/result/admin/`.
9. Install the WordPress ticker plugin (section 5) and add it to the homepage.
10. Confirm the empty state: the ticker shows `LIVE RESULTS → Coming Soon`, and
    the dashboard shows 0 results and 0 students.

Prefer to do it by hand? Copy `config.sample.php` to `config.php`, fill it in,
import `schema.sql` (replacing `{{PREFIX}}` with your prefix) through
phpMyAdmin, then create an admin row with a `password_hash()` value.

## 3. Publishing an internal result

```
Login → Add New Result → fill the form → Save
      → Upload Excel/CSV → map columns → Check File → Import
      → Publish → homepage ticker updates automatically
```

**Add New Result** asks for the institution, examination, class/course,
session, result date, slug, status and ticker setting. An optional logo and
description can be added too.

**Import** is a three-step screen:

1. *Upload* — `.xlsx` or `.csv`, up to 8 MB and 20,000 rows.
2. *Map columns* — the system guesses what each column is; you confirm or
   change it. Only the roll-number column is required. Subject columns can be
   given a per-subject maximum, or paired with a separate "max marks" column
   that carries the same subject name.
3. *Validate & import* — a report shows total rows, valid rows, invalid rows,
   missing roll numbers, duplicates inside the file, and rows that already
   exist in the database. Invalid rows are never imported silently: you must
   tick a confirmation box to import the valid rows and skip the rest.

Re-importing the same file updates the existing students (or skips them, or
replaces everything — your choice on the import options panel).

A result can only be published once it has student records, so an empty result
page can never go live.

### What the system will and will not calculate

* Totals and percentage are derived **only** when you leave the corresponding
  checkbox ticked on the import screen and the file has no such column.
* **Division is never calculated.** It is imported when your file provides it,
  and stays empty otherwise.
* Non-numeric marks (`AB`, `-`, …) are preserved and shown exactly as supplied.

### Example spreadsheet

| Exam Roll No | Registration No. | Student Name | English | Odia | History | Total | Division | Result |
| --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 123456 | ABC123456 | … | 78 | 82 | 75 | 235 | FIRST | PASS |

Subjects, their number and their order are entirely up to you — different
results can have completely different subjects.

## 4. Publishing an external result link

Add New Result → choose **External Result Link** → enter the official result
URL (and optionally a button label) → Publish. No Excel file is asked for and
no student records are created. `/result/<slug>/` redirects to that website,
and the ticker links to it directly.

Only `http://` and `https://` addresses are accepted; `javascript:`, `data:`,
`file:` and malformed URLs are rejected when the result is saved and again
before any link is rendered.

## 5. WordPress homepage ticker

1. Copy `wordpress-plugin/naukripatra-result-ticker/` into
   `wp-content/plugins/` and activate **NaukriPatra Live Results Ticker**.
2. Go to **Settings → Live Results Ticker** and check the path to
   `result/config.php` and the result system URL. The page reports whether it
   can reach the result database.
3. Put the ticker on the homepage with the shortcode:

   ```
   [naukripatra_results_ticker]
   ```

   or, from a theme template:

   ```php
   <?php echo do_shortcode('[naukripatra_results_ticker]'); ?>
   ```

The ticker shows only results with `status = published` **and**
`show_on_ticker = Yes`, newest first, and falls back to
`LIVE RESULTS → Coming Soon` when there are none. Results appear on the
homepage within the cache window (5 minutes by default; set it to 0 for
instant updates).

No WordPress? Include `result/ticker-widget.php` from your theme instead, and
add `public/assets/css/ticker.css` + `public/assets/js/ticker.js` to your
`<head>`. A JSON feed is also available at `/result/ticker.json`.

## 6. Security notes

* All queries use PDO prepared statements; all output is escaped.
* Admin forms are CSRF-protected; sessions are HttpOnly, SameSite=Lax, bound to
  the browser fingerprint, and time out after an hour of inactivity.
* Failed logins are throttled per IP address (8 per 15 minutes by default).
* Uploads are checked for extension, MIME type, size and content signature;
  `uploads/` is configured to refuse script execution, and staged import files
  are stored under opaque random names in a deny-all directory.
* `config.php`, `schema.sql` and `includes/` are blocked by `.htaccess`.
* The public roll-number form is rate-limited so the database cannot be walked.
* Deploy over HTTPS and delete `install.php` after installing.

If your host does not use Apache (`.htaccess` ignored), move `uploads/` and
`includes/` outside the web root or add the equivalent nginx rules:

```nginx
location ~ ^/result/(includes|uploads)/ { deny all; }
location ~ ^/result/(config\.php|schema\.sql)$ { deny all; }
```

## 7. Pretty URLs

`.htaccess` maps `/result/<slug>/` onto `index.php`. If `mod_rewrite` is
unavailable, everything still works through `/result/index.php?slug=<slug>`.
The `RewriteBase /result/` line needs editing only if you install the system
under a different folder.

## 8. File structure

```
result/
├── index.php              front controller: listing, result pages, ticker feed
├── install.php            one-time installer (delete after use)
├── config.sample.php      copy to config.php
├── schema.sql             MySQL schema
├── ticker-widget.php      ticker markup for non-WordPress themes
├── .htaccess
├── admin/                 login, dashboard, add/edit/delete, import, students
│   └── partials/          shared admin markup
├── includes/              bootstrap, database, auth, security, functions,
│                          spreadsheet reader, importer, result form
├── public/                public views, marksheet, assets (css/js)
└── uploads/               logos/ and tmp/ (script execution denied)
```

## 9. Production state

The system ships empty on purpose: no sample results, no demo students, no
placeholder institution. Until you publish your first result the homepage shows
`LIVE RESULTS → Coming Soon` and `/result/` lists nothing.

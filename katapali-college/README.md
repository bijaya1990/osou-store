# KATAPALI +3 COLLEGE, KATAPALI — Demo Website + Admin Panel

A fully demo-filled, colorful government-college style website with a working Admin Panel,
built with plain HTML5 / CSS3 / vanilla JavaScript. No build step, no server, no database required —
everything runs directly in the browser using `localStorage`.

College used in this demo:

```
College Name : KATAPALI +3 COLLEGE, KATAPALI
Address      : AT/PO - KATAPALI, VIA - BIJEPUR, DISTRICT - BARGARH, ODISHA
PIN          : 768032
```

## Folder Structure

```
katapali-college/
├── frontend/            Public website (14 pages)
│   ├── css/style.css
│   ├── js/data.js        <- all demo content + localStorage data layer
│   ├── js/site.js        <- header/footer/sliders/widgets shared by every page
│   └── *.html             Home, About, Academics, Admissions, Faculty, Notices,
│                           Recruitment, Tenders, Examination, Student Corner,
│                           Gallery, Alumni, Downloads, Contact
├── admin-panel/          Admin dashboard (15 pages)
│   ├── css/admin.css
│   ├── js/admin.js        <- auth, sidebar/topbar, generic CRUD engine
│   └── *.html              Login, Dashboard, Notices, Recruitment, Tenders,
│                            Faculty, Gallery, Downloads, Messages, Homepage
│                            Content, Menu Content Editor, Map Settings,
│                            College Info, Theme Customizer, User Management
├── images/                Demo logo, banners, faculty/principal/alumni photos,
│                           gallery photos and demo PDF documents (all SVG/PDF,
│                           no external downloads needed)
└── README.md
```

## Running the Website

No installation needed. Because the site uses `fetch`-free relative paths, you can simply
double-click `frontend/index.html` to open it in a browser. For the most reliable experience
(some browsers restrict `localStorage` on the `file://` origin), serve the folder with any
static file server, for example:

```bash
cd katapali-college
python3 -m http.server 8080
# then open http://localhost:8080/frontend/index.html
```

## Admin Panel Login

Open `admin-panel/index.html` (or click the **Admin Login** button in the site header).

```
Email    : admin@katapalicollege.edu.in
Password : admin123
```

Two more demo accounts are pre-loaded (see Admin Panel → User Management):

```
office@katapalicollege.edu.in / office123   (Editor)
exam@katapalicollege.edu.in   / exam123     (Editor, currently Inactive)
```

## How Content Works (Important)

* All website text, images, lists (notices, recruitment, tenders, faculty, gallery, downloads,
  menu page content, hero slider, principal message, stats, map embed, college info, theme
  colors, users) live in **`localStorage`**, seeded on first load from the defaults in
  `frontend/js/data.js`.
* Editing anything in the Admin Panel updates `localStorage` directly — refresh the public
  website (or open it in the same browser) to see changes immediately.
* Because storage is per-browser, demo edits are local to whichever browser/profile you use.
  For a production deployment, replace the `Store` object in `frontend/js/data.js` with calls
  to a real backend/database — the rest of the site and admin panel already call
  `Store.get/set/add/update/remove`, so only that one file needs to change.

## Replacing Demo Content With Real College Data

1. Log in to the Admin Panel.
2. **College Info Settings** — update the college name, logo, address, phone, email,
   established year and social links (used across header, footer, About and Contact pages).
3. **Homepage Content** — replace hero slider banners, the Principal's photo/message, and
   the quick-stats numbers.
4. **Menu Content Editor** — rewrite the text of every About / Academics / Admissions /
   Examination / Student Corner / Alumni / Downloads / Contact sub-page (HTML editor with
   live preview).
5. **Faculty / Gallery / Notices / Recruitment / Tenders / Downloads** — use the Add / Edit /
   Delete / Search / Filter tools in each module to swap demo entries for real ones. Images and
   PDFs are uploaded via the file picker (stored as base64 data URLs in `localStorage`).
6. **Google Map Settings** — paste a fresh Google Maps "Embed a map" `<iframe>` code and address.
7. **Theme Customizer** — change primary/secondary/accent colors and fonts with a live preview;
   applies to the whole public site instantly.
8. **User Management** — add/remove Admin Panel logins.

No coding is required for any of the above — only the Admin Panel is used.

## Reusing This Template For Another College

Because every visible string, image and color is data-driven, this exact codebase can be
reused for a different college by only editing content through the Admin Panel — no source
files need to change. If you prefer to set new *defaults* baked into the project (so a fresh
browser starts pre-loaded with the new college's data instead of the Katapali demo), edit the
`DEFAULTS` object at the top of `frontend/js/data.js`.

## Technical Notes

* **Frontend**: HTML5, CSS3 (CSS variables for theme), vanilla JavaScript (no framework).
* **Admin Panel**: same stack, with a small generic CRUD engine (`KCAdmin.crud`) in
  `admin-panel/js/admin.js` that renders the Add/Edit modal + data table for every module from
  a declarative field/column config.
* **Icons**: Font Awesome 6 (CDN).
* **Fonts**: Poppins (headings) + Inter (body) via Google Fonts (CDN).
* **Images/PDFs**: all demo assets in `/images` are generated SVG illustrations and minimal
  placeholder PDFs — no external image hosting or stock-photo licensing is required. Replace
  them with real photographs/documents via the Admin Panel at any time.
* **Storage limits**: `localStorage` is typically capped around 5–10 MB per browser origin.
  If you upload many large images, consider compressing them first, or migrate `Store` (in
  `data.js`) to a real backend once ready for production use.

## Demo Data Coverage

Every menu and sub-menu listed in the project brief is pre-filled: About (History, Vision &
Mission, Governing Body, Principal's Desk), Academics (Departments, Courses, Syllabus,
Academic Calendar), Admissions (Process, Eligibility, Fees, Online Enquiry Form), Faculty
(7 homepage sliders + 20 full listing), Notices (6), Recruitment (3), Tenders (2), Examination
(Routine, Results, Rules), Student Corner (Scholarships, Union, Sports/NCC/NSS, Library),
Gallery (16 photos across 4 categories + 3 videos), Alumni (Association + Notable Alumni),
Downloads (10 documents), and Contact (form + map). Nothing is left blank or "Lorem Ipsum".

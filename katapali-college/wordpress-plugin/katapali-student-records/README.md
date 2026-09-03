# Katapali Student Records

A standalone WordPress plugin (works alongside the Katapali +3 College theme) that turns the
college's admission-register Excel/CSV export into:

1. **Student database** (Katapali College &rarr; not required to be active theme, but pulls
   college name/address/logo from the active theme's Customizer settings if it's the Katapali theme).
2. **Public "Student Search / Verification"** shortcode — visitors can look up a student by
   name or roll no. Only shows Name, Roll No, Stream, Batch — nothing sensitive.
3. **Public "Alumni Directory"** shortcode — students grouped by batch year, collapsible per batch.
4. **Admin-only ID Card** — printable, fold-in-half card (front: photo + details, back: rules
   and a renewal table), sized to standard CR80 card width.
5. **Admin-only Library Card** — printable ruled ledger card (Issued to / Date / Book Title rows).

## Install

1. Upload the `katapali-student-records` folder to `wp-content/plugins/`.
2. Activate it under **Plugins**.
3. A new **Student Records** menu appears in wp-admin.

## Import students

1. **Student Records &rarr; Import Students**.
2. Type a **Batch Year** (e.g. `2025-26`) — this groups students for the Alumni Directory.
3. Upload the admission register `.xlsx` (or `.csv`) export.
4. Click **Import**. Re-importing the same file later safely *updates* existing students
   (matched by Roll No) instead of duplicating them.

Recognised columns (case-insensitive): Roll No, Applicant Name, DOB, Mobile, Stream, Sl#,
Barcode Number, Father's/Mother's Name, Aadhaar No, Email ID, Board, Gender, Blood Group,
Address, Category, Religion, SLC No/Date, Marks, Admission Date/Type, Hostel Allot, Subject
Name, TC Date, Amount. Anything else in the file is ignored.

**The Excel file has no photos.** After importing, open each student under **Student
Records &rarr; All Students &rarr; Edit** and upload their photo (used on the ID card).

**Aadhaar numbers** are stored for office records only — the plugin never displays or prints
them anywhere, on any card or public page.

## ID Card / Library Card

From **All Students**, click **ID Card** or **Library Card** next to any student — opens a
printable page in a new tab with a **Print / Save as PDF** button. Admin-login required; there
is no public/self-service download by design (these cards carry photo, DOB, address).

## Shortcodes

Add these to any WordPress Page:

- `[ksr_student_search]` — the search/verification box.
- `[ksr_alumni_directory]` — the batch-wise alumni list.

## Requirements

PHP's Zip extension (`ext-zip`) is needed to read `.xlsx` files directly — nearly every host
has it enabled by default. If a host doesn't, export the admission register as `.csv` instead
and upload that (the importer accepts both).

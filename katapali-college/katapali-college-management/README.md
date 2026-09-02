# Katapali College Management System (plugin)

A companion **plugin** (not a theme) for the Katapali +3 College WordPress site. It adds three fully database-backed systems, all trackable and manageable from `wp-admin`:

1. **Leave Application System** (CL/EL/DL/ML) - teacher/employee portal + principal approval
2. **Certificate/Marksheet Request System** - student portal + office approval
3. **Student ID Card & Library Card System** - Excel/CSV bulk import + printable cards

It is a plugin (not part of the theme) on purpose - it keeps working even if the site's theme is changed later, and it is the correct way to build application functionality (vs. presentation) in WordPress.

## Installation

1. `Plugins -> Add New -> Upload Plugin`, choose `katapali-college-management.zip`, **Install**, then **Activate**.
2. Activation automatically creates 9 database tables (`wp_kcms_*`) and two new user roles: **Teacher / Employee** and **Student**.
3. A new **College Management** menu appears in the wp-admin sidebar (Dashboard, Employees, Leave Applications, Students, Certificate Requests, ID Cards, Uploads Log, Settings).

## Setting it up

### 0. Login page (do this first)
- Create one page (e.g. "Login") with the shortcode `[kcms_login]`, and one page (e.g. "My Dashboard") with `[kcms_my_dashboard]`.
- Go to **College Management -> Settings** and select both pages under **Login & Portal Pages**.
- That's it - there is no separate "create a WordPress account" step for teachers or students. The login page has two tabs (Teacher/Staff, Student) and asks only for **Mobile Number + Date of Birth** (a calendar picker). The first time someone logs in successfully, their WordPress account is created automatically behind the scenes and linked to their record - nothing for the office to set up per person beyond entering their mobile number and DOB (below). Logging in always lands on the Portal page, never on wp-admin.
- Both fields are checked against the mobile/DOB already on file, so **every Employee and Student record must have both filled in** (the admin forms below require them) for that person to be able to log in. 6 wrong attempts locks that login type out for 15 minutes.

### 1. Leave Application System
- Go to **College Management -> Employees** and add each teacher/employee (name, designation, department, mobile number, date of birth - the last two are what they'll log in with). Email is optional, but recommended for OTP delivery.
- Create a page (e.g. "Leave Application") and add the shortcode `[kcms_leave_form]` to it. Share this page's link with your staff.
- Applications appear under **College Management -> Leave Applications** for you (Principal/Admin) to approve or reject, with an optional signature upload.

### 2. Certificate / Marksheet Request System
- Student master data is usually loaded via the ID Card system's Excel/CSV import (below), which also creates a matching row here automatically. You can also add/edit students directly under **College Management -> Students** (mobile number and date of birth are required there too, for login).
- Create a page (e.g. "Certificate Request") with the shortcode `[kcms_certificate_form]`.
- Requests appear under **College Management -> Certificate Requests** to approve, reject, or mark issued.

### 3. ID Card & Library Card System
- Go to **College Management -> ID Cards**, download the upload template (.csv), fill it in (or upload a `.xlsx` file with the same 13 columns - **mobile and dob are what the student logs in with**), and upload it. Re-uploading with the same roll number updates that student instead of duplicating them.
- Upload each student's photo individually from the same screen (bulk photo-zip upload is not included in this version - upload one at a time under each student's "Photo" button).
- Click "Mark ID Generated" once a card is ready; students see a "Ready" badge in their own portal.
- Every student can preview/print/download their own ID card and Library card via `[kcms_my_id_card]`, or the combined `[kcms_my_dashboard]` shortcode (also shows their leave/certificate history).

### PDF documents
All three systems generate a professional, print-ready HTML page (A4 for leave/certificate, A5 for the ID card, A6 for the library card) with a **"Print / Save as PDF"** button - browsers can save these directly as a PDF via their built-in print dialog. This avoids installing a heavy PDF-generation library on free/shared hosting.

### OTP verification
Both the Leave and Certificate portals require a 6-digit OTP before the form can be submitted. By default the OTP is **emailed** to the applicant (via WordPress's own mail sending) - no extra setup needed. To send **real SMS** instead, go to **College Management -> Settings** and add your **MSG91** or **Twilio** account credentials; once configured, OTPs are sent by SMS automatically (email OTP stays as the automatic fallback if SMS sending fails).

## A note on Mobile Number + Date of Birth login

This is easier than a username/password for students and staff who won't remember a separate portal password, but it is inherently weaker than a real password (a DOB has limited possible values, and it's sometimes known to people other than the account holder). The lockout (6 wrong attempts = 15 minutes) limits guessing, and every login is logged in the audit log, but if a college wants stronger protection later, `KCMS_Login::handle_login()` is the one place to add a second factor (e.g. requiring the OTP flow from `KCMS_OTP` before completing login too).

## Security

- Mobile numbers are stored **encrypted** (AES-256) in the database and only ever shown in full inside the admin panel; everywhere else (student/teacher self-view) they are masked (`XXXXXX3210`).
- OTP codes are hashed, expire after 10 minutes, and lock out after 5 wrong attempts.
- Every form submission, approval, rejection, and bulk import is written to an internal audit log (`wp_kcms_audit_log`).
- All database queries use `$wpdb->prepare()`; all admin actions require the correct capability (`kcms_manage_leave` / `kcms_manage_certificates` / `kcms_manage_idcards` / `kcms_manage_settings` - administrators have all four automatically) plus a WordPress nonce.
- Print/PDF pages for a specific leave application, certificate request, or ID card can only be opened by that record's own owner or by an admin - anyone else gets a 403.

## Notes / current scope

- Excel upload accepts real **`.xlsx`** files (parsed natively via PHP's built-in ZipArchive - no external library) as well as plain `.csv`.
- QR/barcode images on the ID card and Library card are generated via a free third-party QR API (`api.qrserver.com`) at print time - only the roll number/member ID is sent to it, no personal data. Swap this for a local QR library later if you'd rather not call an external service.
- This is a first, fully working version of all three systems end-to-end (tested on a real WordPress install: activation, all three submission flows, OTP, admin approvals, Excel import, and all four print templates). Bulk photo upload (one zip of many photos at once) and SMS delivery reports are natural next additions if you need them later.

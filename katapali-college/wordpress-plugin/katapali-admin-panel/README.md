# Katapali Admin Panel

Gives college staff a custom-branded, limited-access login instead of handing
out full WordPress administrator accounts.

## What it does

- **Reskins the login page** (the "Admin Login" button already on the
  site) with the college's own logo, name, address and an "Admin Panel"
  label, in the site's brand colours - no WordPress logo/branding.
- **Reskins wp-admin itself** for staff accounts: brand-coloured sidebar
  menu and toolbar, a logo/name banner above the menu, and the WordPress
  "Thank you for creating with WordPress" footer replaced with the
  college's name.
- **Trims the admin menu** to exactly: Katapali College (Hero Slides,
  Notices, Recruitment, Tenders, Faculty, Gallery, Downloads, Links,
  Organisation Logos, Applications), Posts, Student Records (needs the
  Katapali Student Records plugin), and Media. Pages, Comments,
  Appearance, Plugins, Users, Tools, and Settings are hidden.
- **"Login Successful" welcome popup** the moment a staff account signs in.
- **Add College Admin** (Katapali College &rarr; Add College Admin, real
  site administrators only): fill in Name / User ID / Mobile / Email /
  Password &rarr; the account is created immediately (same as WordPress's
  own Users &rarr; Add New) &rarr; share that User ID and password with the
  person directly. A welcome email ("Welcome to &lt;college&gt;! You are
  now an admin of this college website.") is also attempted, but it's
  best-effort and never blocks account creation.

## Requirements

The welcome email uses WordPress's standard `wp_mail()`. Most hosts
(including MilesWeb) send this fine out of the box, but if it doesn't
arrive that's fine to ignore - the account itself doesn't depend on it. If
you'd like it to arrive reliably, install an SMTP plugin (e.g. WP Mail
SMTP) and connect it to a real mailbox/SMTP service - this is a
hosting/mail-deliverability setting, not something this plugin can fix in
code.

## Notes

- Full WordPress administrators (`manage_options`) are never restricted by
  this plugin - only accounts created through **Add College Admin** get the
  limited "College Staff Admin" role and the reskinned experience.
- The limited role can manage all the content types listed above but
  cannot install plugins/themes, change site settings, or manage other
  users' accounts.

| `public/index.php` | Router (`/`, service pages, `/locations/{slug}/`, `/lp/{slug}/`, `/blog/`, static pages, `/booking/`, `/virtual-consultation/`, `/thank-you/`, `/book`, `/book-virtual`, `/admin/`, `/sitemap.xml`) |
| `docs/` | Setup notes for things that need an account elsewhere (Google Calendar) |
# Ignite Orthodontics website

PHP site for https://igniteorthodontics.com, hosted on Hostinger.

## Layout

| Path | What it is |
| --- | --- |
| `*.html` (repo root) | Design source files (header, home, location template, kids page) |
| `tools/extract.php` | Builds `public/assets/{css,js}` and the generated views from those source files |
| `public/` | Everything that is deployed to `public_html` |
| `public/index.php` | Router (`/`, service pages, `/locations/{slug}/`, `/blog/`, static pages, `/booking/`, `/thank-you/`, `/book`, `/admin/`, `/sitemap.xml`) |
| `public/app/templates.php` | Page layouts: the sections and fields each layout offers the dashboard |
| `public/app/data/` | Booking choices and the remaining static pages; `seed-pages.php` and `posts/*.php` fill the database once |
| `public/app/views/` | Public layout, pages and partials |
| `public/app/views/templates/` | The three layouts that draw a page's sections (classic, spotlight, simple) |
| `public/app/admin/` | Admin dashboard router, controllers and views |
| `public/admin-assets/` | Dashboard CSS/JS and the self-hosted TinyMCE 7 editor (GPL-2.0-or-later) |

After editing a design source file, run `php tools/extract.php` and commit the regenerated files.
It rebuilds the header, the home page and the stylesheets. The office and kids-braces
layouts are now CMS templates, so only their CSS and JS come from the source files.

## Run locally

XAMPP's PHP has the needed extensions (SQLite, cURL, fileinfo); load GD for image processing:

```
/c/xamppnew/php/php.exe -d extension=gd -S localhost:8080 -t public public/index.php
```

## Deploy

Push to `main`, then on the server:

```
ssh ignite '~/ignite-deploy/deploy.sh'
```

The script pulls `main`, backs up `public_html` and the CMS database to `~/ignite-backups/`
(last 10 of each kept) and syncs `public/`. It never touches `app/config.local.php` or `uploads/`.

## Admin dashboard (pages, blog, media)

Sign in at https://igniteorthodontics.com/admin/.

- **Service Pages / Locations:** create a page, choose a layout, then fill in its sections.
  Each layout (`app/templates.php`) decides which sections exist and which fields they have,
  so the editor form, the saved content and the published page always match. Sections can be
  switched off, repeating rows (cards, offers, questions) can be added and reordered, and
  Preview opens the page as it looks right now, including unsaved changes. Renaming the
  address of a live page leaves a redirect behind. Office pages also feed the locations menu,
  the office list, the footer and the booking page; `{office}` in any text becomes the office name.
- **Treatment Guide layout:** long-form service pages built from blocks (feature cards, steps,
  side-by-side panels, comparison table, cost and payment, doctor, reviews, call-to-action, FAQ)
  that can be added, reordered and removed. Blocks are defined in `app/guide-blocks.php`.
  A service page can sit under another one (Parent page), for example `/types-of-braces/ceramic-braces/`.
- **Landing Pages (`/lp/...`):** ad landing pages, for example `/lp/farmingtonhills/braces-99/`.
  They use the Landing Page layout: a slim header and footer with no site menu, one chosen office
  (its phone, address and a booking link that pre-selects it) and the same blocks as the Treatment
  Guide. They are hidden from Google and left out of the sitemap unless the "Google" setting says to show them.
  A landing page can sit under another one (Parent page), one level deep.
- **Importing written content:** `php app/cli/import-pages.php app/data/content/FILE.php [--dry-run]`
  saves pages through the same cleaning as the editor. Pages edited in the dashboard are skipped
  unless `--force` is given. The content file's `type` and `template` decide the kind of page
  (`lp-farmington-hills.php` holds the Farmington Hills landing pages).
- **Posts:** WordPress-style editor with Save Draft, Preview, Publish or Schedule, topic,
  featured image, summary and FAQ. Content is cleaned on save: links and images that point
  to other websites are removed (the site never sends visitors off-site).
- **Media Library:** drag-and-drop upload or import from a URL (the image is downloaded,
  never hotlinked). Images are validated, re-encoded and stored in `public_html/uploads/`.
- **Bookings:** every consultation request, newest first, with status tabs (New / Contacted /
  Appointment booked / Closed / Trash), an office filter, search by name, phone or email, a
  note field per request and a CSV download. The sidebar shows how many are new. Requests can
  be added by hand (phone and walk-in enquiries), edited, trashed, restored and deleted.
  They live in the `bookings` table (`app/bookings.php`); anything that only reached
  `bookings.jsonl` is imported when the screen is opened, and a deleted one is not brought back.
- **Settings → Virtual consultations:** the video consultation page (`/virtual-consultation/`)
  offers the times that are free on a Google Calendar and books them with a Google Meet link
  (`app/consult.php`, `app/google-calendar.php`, `app/book-virtual.php`). Set the calendar, the
  Workspace user to book as, the call length, how far ahead and the hours offered; the screen says
  whether Google is actually answering. Connecting it is described in `docs/google-calendar-setup.md`.
  Until it is connected the page asks the visitor to request a time instead, so it never errors.
- **Settings → Booking notifications:** who is emailed when a consultation request comes in —
  one list that receives every booking plus extra addresses per office (`app/notify.php`,
  settings key `booking_emails`). "Save and send a test" mails a sample request, marked as a test.
  `lead_email` in `app/config.local.php` still works and is added to the everything list.

Data lives outside the web root in `~/domains/igniteorthodontics.com/ignite-data/cms.sqlite`.

Create a login, or reset a forgotten password (prints a new password once):

```
ssh ignite 'cd ~/domains/igniteorthodontics.com/public_html && php app/cli/create-admin.php you@example.com "Your Name"'
```

## Booking requests

Every "Schedule Now" / "Request a Consultation" button links to the booking page, `/booking/`
(`app/views/booking.php`, `assets/js/booking.js`). On an office page the links are
`/booking/?office=slug`: that office is fixed and its step is skipped (`booking_url()` builds
the links). `POST /book` validates the request, appends it to `ignite-data/bookings.jsonl`
(with the page the visitor came from as `source`) and the page moves on to `/thank-you/`.
The request is then saved to the `bookings` table for the dashboard's **Bookings** screen and
emailed to the addresses in **Settings → Booking notifications** (the everything list plus
that office's own addresses); each recipient gets their own copy, so they
never see each other. A mail failure never fails the booking — the request is already saved.
`lead_email` in `public_html/app/config.local.php` still works and is added to the everything list.

Requests that were saved before the addresses were set up can be emailed afterwards:

```
ssh ignite 'cd ~/domains/igniteorthodontics.com/public_html && php app/cli/resend-bookings.php --dry-run'
```

(`--since=YYYY-MM-DD`, `--id=`, `--to=` narrow it; each email is marked as an earlier request.)

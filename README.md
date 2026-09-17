# Ignite Orthodontics website

PHP site for https://igniteorthodontics.com, hosted on Hostinger.

## Layout

| Path | What it is |
| --- | --- |
| `*.html` (repo root) | Design source files (header, home, location template, kids page) |
| `tools/extract.php` | Builds `public/assets/{css,js}` and the generated views from those source files |
| `public/` | Everything that is deployed to `public_html` |
| `public/index.php` | Router (`/`, service pages, `/locations/{slug}/`, `/blog/`, static pages, `/thank-you/`, `/book`, `/admin/`, `/sitemap.xml`) |
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
  the office list, the footer and the booking popup; `{office}` in any text becomes the office name.
- **Treatment Guide layout:** long-form service pages built from blocks (feature cards, steps,
  side-by-side panels, comparison table, cost and payment, doctor, reviews, call-to-action, FAQ)
  that can be added, reordered and removed. Blocks are defined in `app/guide-blocks.php`.
  A service page can sit under another one (Parent page), for example `/types-of-braces/ceramic-braces/`.
- **Importing written content:** `php app/cli/import-pages.php app/data/content/FILE.php [--dry-run]`
  saves pages through the same cleaning as the editor. Pages edited in the dashboard are skipped
  unless `--force` is given.
- **Posts:** WordPress-style editor with Save Draft, Preview, Publish or Schedule, topic,
  featured image, summary and FAQ. Content is cleaned on save: links and images that point
  to other websites are removed (the site never sends visitors off-site).
- **Media Library:** drag-and-drop upload or import from a URL (the image is downloaded,
  never hotlinked). Images are validated, re-encoded and stored in `public_html/uploads/`.

Data lives outside the web root in `~/domains/igniteorthodontics.com/ignite-data/cms.sqlite`.

Create a login, or reset a forgotten password (prints a new password once):

```
ssh ignite 'cd ~/domains/igniteorthodontics.com/public_html && php app/cli/create-admin.php you@example.com "Your Name"'
```

## Booking requests

Every "Schedule Now" / "Request a Consultation" button opens the booking popup
(`app/views/partials/booking.php`, `assets/js/booking.js`). `POST /book` validates the
request, appends it to `ignite-data/bookings.jsonl` and redirects to `/thank-you/`.
To also email each request, create `public_html/app/config.local.php` from
`config.local.php.example` and set `lead_email`.

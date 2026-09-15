# Ignite Orthodontics website

PHP site for https://igniteorthodontics.com, hosted on Hostinger.

## Layout

| Path | What it is |
| --- | --- |
| `*.html` (repo root) | Design source files (header, home, location template, kids page) |
| `tools/extract.php` | Builds `public/assets/{css,js}` and the generated views from those source files |
| `public/` | Everything that is deployed to `public_html` |
| `public/index.php` | Router (`/`, `/locations/{slug}/`, `/braces-for-kids/`, `/blog/`, content pages, `/thank-you/`, `/book`, `/admin/`, `/sitemap.xml`) |
| `public/app/data/` | Offices, page content and booking choices; `posts/*.php` only seed the blog database once |
| `public/app/views/` | Public layout, pages and partials |
| `public/app/admin/` | Admin dashboard router, controllers and views |
| `public/admin-assets/` | Dashboard CSS/JS and the self-hosted TinyMCE 7 editor (GPL-2.0-or-later) |

After editing a design source file, run `php tools/extract.php` and commit the regenerated files.

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

## Admin dashboard (blog + media)

Sign in at https://igniteorthodontics.com/admin/.

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

# Ignite Orthodontics website

PHP site for https://igniteorthodontics.com, hosted on Hostinger.

## Layout

| Path | What it is |
| --- | --- |
| `*.html` (repo root) | Design source files (header, home, location template, kids page) |
| `tools/extract.php` | Builds `public/assets/{css,js}` and the generated views from those source files |
| `public/` | Everything that is deployed to `public_html` |
| `public/index.php` | Router (`/`, `/locations/{slug}/`, `/braces-for-kids/`, content pages, `/thank-you/`, `/book`, `/sitemap.xml`) |
| `public/app/data/` | Offices, page content and booking choices (move to MySQL with the admin CMS) |
| `public/app/views/` | Layout, pages and partials |

After editing a design source file, run `php tools/extract.php` and commit the regenerated files.

## Run locally

```
php -S localhost:8080 -t public public/index.php
```

## Deploy

Push to `main`, then on the server:

```
ssh ignite '~/ignite-deploy/deploy.sh'
```

The script pulls `main`, backs up `public_html` to `~/ignite-backups/` (last 10 kept) and syncs `public/`.
It never touches `app/config.local.php` or `uploads/`.

## Booking requests

Every "Schedule Now" / "Request a Consultation" button opens the booking popup
(`app/views/partials/booking.php`, `assets/js/booking.js`): who it is for, treatment,
office, preferred day and time, then contact details. Anything with `data-book`
(optionally `data-book-office="slug"`) or a link to `#ig-consult` opens it, as does
`/?book=1&office=slug`. Office pages preselect their own office.

`POST /book` (`app/book.php`) validates the request, appends it to
`~/domains/igniteorthodontics.com/ignite-data/bookings.jsonl` (outside the web root)
and redirects the visitor to `/thank-you/`.

To also email each request, create `public_html/app/config.local.php` from
`config.local.php.example` and set `lead_email`.

# Ignite Orthodontics website

PHP site for https://igniteorthodontics.com, hosted on Hostinger.

## Layout

| Path | What it is |
| --- | --- |
| `*.html` (repo root) | Design source files (header, home, location template, kids page) |
| `tools/extract.php` | Builds `public/assets/{css,js}` and the generated views from those source files |
| `public/` | Everything that is deployed to `public_html` |
| `public/index.php` | Router (`/`, `/locations/{slug}/`, `/braces-for-kids/`, content pages, `/consult`, `/sitemap.xml`) |
| `public/app/data/` | Offices and page content (moves to MySQL with the admin CMS) |
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

## Form submissions

Consultation requests are validated by `app/consult.php` and appended to
`~/domains/igniteorthodontics.com/ignite-data/leads.jsonl` (outside the web root).
To also email them, create `public_html/app/config.local.php` from `config.local.php.example`.

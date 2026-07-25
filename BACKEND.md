# Pentagon Quest – PHP Backend

Fully working backend for the Pentagon Quest travel site: admin panel, client tour tracking, SMTP, migrations, and public APIs.

## Folder layout

```
public/          <- becomes your cPanel / AMPPS document root
                     (folder name: "Pentagon Quest UI" or public_html)
  index.html       static homepage + all the scraped static pages
  destinations/, packages/, about-us/, ...
  css/, js/, wp-content/, wp-includes/
  uploads/         web-accessible upload storage
  admin/           admin panel (PHP)
  client/          client portal (PHP)
  api/             public JSON endpoints (PHP)
  devs/            admin registration page (PHP)

apps/             <- stays OUTSIDE the web root, not browsable
  backend/
    bootstrap.php
    config.php     DB / SMTP / app config
    lib/           Auth, Database, Mailer, Migrator, Settings, helpers
    migrations/
  data/            SQLite DB file lives here if you use the sqlite driver

scripts/          one-off maintenance scripts (not deployed, not web-served)
```

`public/admin`, `public/client`, and `public/api` all `require` the shared
engine from `apps/backend/bootstrap.php` (two directories up). They must
stay web-accessible since the browser hits them directly (each has its own
login/auth), while `apps/` holds the sensitive bits — DB credentials,
shared classes, migrations — that never need to be requested by URL.

## Quick start (local)

```bash
# 1) Start PHP server from the project root
php -S localhost:8080 router.php

# 2) Open migrations and run them
http://localhost:8080/admin/migrate.php

# 3) Register the first admin (devs page)
http://localhost:8080/devs/register.php

# 4) Sign in
http://localhost:8080/admin/login.php
```

When the site lives in the **`Pentagon Quest UI`** folder (AMPPS/XAMPP), prefix every
path with that folder — full rules are in **[RULES.md](RULES.md)**.

Client portal: `http://localhost:8080/client/login.php`

`router.php` simply mirrors what Apache does in production: it serves
everything out of `public/`, so `php -S localhost:8080 -t public` also
works directly without the router script.

## Deploying to cPanel

1. Upload the **contents** of `public/` into your domain's document root
   (usually `public_html/`, or the docroot you set for an add-on/subdomain).
2. Upload `apps/` as a **sibling** of `public_html` — i.e. one level above
   it, in your account's home directory — so it sits outside the web root
   and can't be requested by URL. Example layout on the server:
   ```
   /home/youruser/
     apps/               <- from this repo's apps/
     public_html/        <- contents of this repo's public/
   ```
   If your host lets you pick a custom document root for the domain,
   you can instead upload the whole repo and just point the docroot at
   `public/` — then `apps/` is automatically outside the served path.
3. Create a MySQL database + user in cPanel, then edit
   `apps/backend/config.php` (or set `DB_HOST`/`DB_NAME`/`DB_USER`/`DB_PASS`
   env vars) with those credentials.
4. Visit `/admin/migrate.php` once to create the schema, then
   `/devs/register.php` to create the first admin.
5. Make sure `apps/data/` (if using SQLite) and `public/uploads/` are
   writable by the PHP process.

## Features

| Area | Path | What it does |
|------|------|----------------|
| Admin login | `/admin/login.php` | Secure admin authentication |
| Register admin | `/devs/register.php` | First admin open; later admins require login |
| Migrations | `/admin/migrate.php` | Creates SQLite/MySQL schema + sample data |
| Travels | `/admin/travels.php` | CRUD destinations |
| Tours | `/admin/tours.php` | CRUD packages |
| Client requests | `/admin/requests.php` | Manage inquiries + reply by email |
| Bookings | `/admin/bookings.php` | Create bookings, progress, client updates |
| Clients | `/admin/clients.php` | Manage client accounts |
| Send email | `/admin/emails.php` | Compose + SMTP log |
| SMTP settings | `/admin/settings.php` | Host, port, TLS, credentials, test mail |
| Forgot password | `/admin/forgot-password.php` | Admin password reset |
| Client portal | `/client/` | Register, login, track tours, forgot password |
| Public APIs | `/api/` | packages, travels, contact, newsletter, track |

## Database

Default driver is **MySQL** (`apps/backend/config.php`), reading
`DB_HOST` / `DB_PORT` / `DB_NAME` / `DB_USER` / `DB_PASS` env vars with
`127.0.0.1` / `root` / (empty) as local fallbacks.

To use SQLite instead, set `'driver' => 'sqlite'` in
`apps/backend/config.php`. The file lives at `apps/data/pentagon_quest.sqlite`
(outside the web root either way).

## SMTP

Configure under **Admin → SMTP Settings**, or set env vars:

- `SMTP_HOST`, `SMTP_PORT`, `SMTP_ENCRYPTION`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM`, `SMTP_FROM_NAME`

If SMTP is not configured, password-reset pages still show a one-time link for local setup.

## Contact form

The site posts inquiries to `POST /api/contact.php`. Responses appear in **Admin → Client Requests**.

## Production notes

- Point the web root at `public/` (Apache/Nginx with PHP).
- Ensure `apps/data/` and `public/uploads/` are writable by the PHP user.
- Restrict `/admin/migrate.php` after go-live if desired.
- Prefer HTTPS so session cookies stay secure.

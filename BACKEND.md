# Pentagon Quest – PHP Backend

Fully working backend for the Pentagon Quest travel site: admin panel, client tour tracking, SMTP, migrations, and public APIs.

## Quick start

```bash
# 1) Start PHP server from the project root
php -S localhost:8080 router.php

# 2) Open migrations and run them
http://localhost:8080/admin/migrate.php

# 3) Register the first admin
http://localhost:8080/admin/register.php

# 4) Sign in
http://localhost:8080/admin/login.php
```

Client portal: `http://localhost:8080/client/login.php`

## Features

| Area | Path | What it does |
|------|------|----------------|
| Admin login | `/admin/login.php` | Secure admin authentication |
| Register admin | `/admin/register.php` | First admin open; later admins require login |
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

Default is **SQLite** at `data/pentagon_quest.sqlite` (no MySQL required).

To use MySQL, edit `backend/config.php`:

```php
'db' => [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'pentagon_quest',
    'username' => 'root',
    'password' => '',
],
```

## SMTP

Configure under **Admin → SMTP Settings**, or set env vars:

- `SMTP_HOST`, `SMTP_PORT`, `SMTP_ENCRYPTION`, `SMTP_USER`, `SMTP_PASS`, `SMTP_FROM`, `SMTP_FROM_NAME`

If SMTP is not configured, password-reset pages still show a one-time link for local setup.

## Contact form

The site posts inquiries to `POST /api/contact.php`. Responses appear in **Admin → Client Requests**.

## Production notes

- Point the web root at this project (Apache/Nginx with PHP).
- Ensure `data/` and `uploads/` are writable by the PHP user.
- Restrict `/admin/migrate.php` after go-live if desired.
- Prefer HTTPS so session cookies stay secure.

# Pentagon Quest UI

Travel website with a full PHP backend. The web root folder name is **`Pentagon Quest UI`**.

- **`public/`** — everything the browser reaches: static pages, `admin/`, `client/`, `api/`, `devs/`. Upload these *contents* into your `Pentagon Quest UI` (or `public_html`) folder.
- **`apps/`** — backend engine (DB config, libraries, migrations). Keep as a sibling outside the web root.

**Path & access rules (admin login + admin registration):** see **[RULES.md](RULES.md)**  
**Backend setup:** see **[BACKEND.md](BACKEND.md)**

## Local AMPPS / XAMPP

Put `public/` contents into `www/Pentagon Quest UI/`, keep `apps/` beside that folder (or one level above).

1. `http://localhost/Pentagon Quest UI/admin/migrate.php`
2. `http://localhost/Pentagon Quest UI/devs/register.php` — create first admin
3. `http://localhost/Pentagon Quest UI/admin/login.php` — sign in

## PHP built-in server

```bash
php -S localhost:8080 router.php
```

- Site: `http://localhost:8080/`
- Admin login: `http://localhost:8080/admin/login.php`
- Register admins: `http://localhost:8080/devs/register.php`
- Client portal: `http://localhost:8080/client/login.php`

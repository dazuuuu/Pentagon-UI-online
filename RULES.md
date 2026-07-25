# Pentagon Quest UI – Path & Access Rules

The website root folder name is **`Pentagon Quest UI`**.

All admin / client / API / developer URLs hang off that folder. Spaces in the
folder name are fine in the browser (`Pentagon Quest UI` or `Pentagon%20Quest%20UI`).

## Base URL

| Environment | Site root |
|-------------|-----------|
| Local AMPPS / XAMPP | `http://localhost/Pentagon Quest UI/` |
| PHP built-in server (`php -S localhost:8080 router.php`) | `http://localhost:8080/` (auto-detected, no subfolder) |
| Production (contents of `public/` in docroot) | `https://your-domain/` or `https://your-domain/Pentagon Quest UI/` |

PHP builds links with `url()` / `base_path()` in `apps/backend/lib/helpers.php`.
JS resolves the same prefix in `public/js/api.js` and `public/js/forms.js`.

Override with env var if needed:

```bash
PQ_BASE_PATH="/Pentagon Quest UI"
```

---

## Admin login

**URL**

```
http://localhost/Pentagon Quest UI/admin/login.php
```

**Rules**

1. Run migrations once before first login:  
   `http://localhost/Pentagon Quest UI/admin/migrate.php`
2. Create the first admin via the **devs** page (below), then sign in here.
3. Session cookie name: `pq_session` (see `apps/backend/config.php`).
4. Forgot password: `admin/forgot-password.php` (needs SMTP, or shows a one-time local reset link).
5. Sign out: `admin/logout.php`.
6. After login, dashboard is `admin/index.php`. Unauthenticated visits to any
   other `admin/*.php` page redirect back to login.

---

## Devs page – register admins

**URL**

```
http://localhost/Pentagon Quest UI/devs/register.php
```

**Rules**

1. **First admin (open registration)**  
   When the `admins` table is empty and `allow_open_admin_register` is `true`
   in `apps/backend/config.php`, anyone who can open this URL may create the
   first **superadmin**. No prior login required.
2. **Later admins (closed registration)**  
   After at least one admin exists, this page requires an **already signed-in
   admin**. Anonymous visitors are redirected to `admin/login.php`.
3. Password must be at least **8 characters**; email must be unique.
4. CSRF token is required on the form.
5. On success, the new admin is created and (if you were not already signed in)
   you are logged in and sent to `admin/index.php`.
6. Legacy stub at repo-root `devs/register.php` only redirects into the public URL above.

---

## Quick setup order

1. Put **`public/` contents** into the web folder named `Pentagon Quest UI`  
   (keep `apps/` as a sibling outside the web root, or one level above `public_html`).
2. Configure DB in `apps/backend/config.php` (MySQL default for AMPPS).
3. Open **`/Pentagon Quest UI/admin/migrate.php`** → run migrations.
4. Open **`/Pentagon Quest UI/devs/register.php`** → create first admin.
5. Open **`/Pentagon Quest UI/admin/login.php`** → sign in.

---

## Other useful paths

| Page | Path under root folder |
|------|------------------------|
| Homepage | `/` or `/index.html` |
| Admin dashboard | `/admin/index.php` |
| Client portal login | `/client/login.php` |
| Public API | `/api/packages.php`, `/api/contact.php`, … |

See **BACKEND.md** for full backend layout and SMTP notes.

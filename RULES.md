# Pentagon Quest UI – Path & Access Rules

The website root folder name is **`Pentagon Quest UI`**.

All requests are routed by one file: **`path-handler.php`** (also used by root `index.php` and `router.php`). It maps pretty URLs into the real files under `public/`.

## How paths work

Keep the **whole project** in your AMPPS/XAMPP folder named `Pentagon Quest UI`:

```
www/Pentagon Quest UI/
  path-handler.php   ← single path handler
  index.php
  .htaccess
  admin/             ← thin proxies → public/admin/
  devs/
  client/
  api/
  public/            ← real site + PHP apps
  apps/              ← backend (not browsable)
```

Apache uses `.htaccess` → `path-handler.php`.  
If rewrite is off, these still work:

```
http://localhost/Pentagon Quest UI/path-handler.php/admin/login.php
http://localhost/Pentagon Quest UI/path-handler.php/devs/register.php
http://localhost/Pentagon Quest UI/admin/login.php
http://localhost/Pentagon Quest UI/devs/register.php
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
2. Create an admin via the **devs** page, then sign in here.
3. Unauthenticated visits to other `admin/*.php` pages redirect here.
4. Forgot password: `admin/forgot-password.php`
5. Sign out: `admin/logout.php`
6. Dashboard after login: `admin/index.php`

---

## Devs page – register admins

**URL**

```
http://localhost/Pentagon Quest UI/devs/register.php
```

**Rules**

1. **First admin:** open when no admins exist (`allow_open_admin_register` = true).
2. **Later admins:** requires an already signed-in admin; others redirect to login.
3. Password at least 8 characters; email must be unique.
4. CSRF token required.

---

## Quick setup order

1. Put this whole repo in `www/Pentagon Quest UI/` (do **not** upload only `public/`).
2. Ensure Apache `mod_rewrite` + `AllowOverride All` (AMPPS default is usually fine).
3. Open `…/admin/migrate.php` → run migrations.
4. Open `…/devs/register.php` → create first admin.
5. Open `…/admin/login.php` → sign in.

## Local PHP server (optional)

```bash
php -S localhost:8080 router.php
```

Then use `http://localhost:8080/admin/login.php` (no folder prefix).

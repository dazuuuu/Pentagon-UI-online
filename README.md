# Pentagon Quest

Travel website with a full PHP backend, laid out for cPanel hosting:

- **`public/`** — everything the browser reaches directly: the static site (destinations, packages, etc.), and the admin/client/API apps. This folder's *contents* become your cPanel document root (`public_html` or an add-on domain's web root).
- **`apps/`** — the backend engine (DB config, shared library, migrations) and the SQLite data folder. Kept as a sibling of `public/`, outside the web root, so it's never directly browsable.

See **[BACKEND.md](BACKEND.md)** for setup and deployment details.

## Local development

```bash
php -S localhost:8080 router.php
```

- Site: `http://localhost:8080/`
- Admin: `http://localhost:8080/admin/`
- Client tour tracking: `http://localhost:8080/client/`
- APIs: `http://localhost:8080/api/`

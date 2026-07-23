# Mascardi Lifestyle

E-commerce + benefits ecosystem website for Mascardi Lifestyle. Plain PHP 8.1+/MySQL, no framework, built to deploy on cPanel shared hosting. See `.claude` plan history for the full architecture; this file covers local setup and current status.

## Status: Phase 3 complete (Shop, cart, checkout, M-Pesa)

**Phase 1 — Admin backend**: authentication, dashboard, Pillars CRUD, Partners CRUD, and Site Settings (hero video/overlay text/footer/social), all with secure image upload.

**Phase 2 — Public homepage**: full black & white homepage live at `/` — sticky header with mobile nav, autoplaying muted-loop YouTube hero with overlay text (both editable from Settings), the 8-pillar grid pulling live from the Pillars admin module, a Partners logo grid with a graceful empty state until partners are added, and a content-driven footer. AOS scroll-reveal and vanilla-tilt hover effects are wired in (both self-hosted, no CDN), and everything respects `prefers-reduced-motion`.

**Phase 3 — Shop Mascardi**: full e-commerce loop. Admin: Product Categories, Products (multi-image upload, pricing, stock, featured flag) under **Admin → Products**, and an Orders module (filter by status, per-order detail with M-Pesa transaction info, manual status/notes) under **Admin → Orders**. Public: the homepage carousel (4×2 paginated slides, real "Add to Cart" buttons) now pulls live featured products, plus a full `/shop` catalog with category filters and `/shop/{slug}` product detail pages. Session-based cart at `/cart`, guest checkout at `/checkout` (upserts a lightweight customer record by phone), and a direct **M-Pesa Daraja STK Push** integration — `app/Services/Mpesa/` (`DarajaClient`, `StkPushService`, `StkQueryService`, `CallbackHandler`) handles OAuth token caching, push initiation, the public webhook (`public/mpesa/callback.php`), and idempotent, row-locked stock decrement on confirmed payment. The waiting-for-payment page polls status and offers a retry if the push never went through.

**Not yet live**: real Daraja credentials — `config/mpesa.php` still has placeholder sandbox values, so STK push initiation will fail until real Consumer Key/Secret/Shortcode/Passkey are added (see "M-Pesa go-live" below). Events (Phase 4) is still a placeholder section, followed by polish/go-live (Phase 5).

> Note: I validated Phases 2–3 by rendering pages server-side, running full HTTP request flows (admin CRUD round-trips, add-to-cart → checkout → simulated M-Pesa callback → stock decrement, verified idempotent on a duplicate callback), and checking logs/HTML for errors — no PHP warnings anywhere, CSRF protection confirmed on all public POST routes. I don't have a browser tool in this environment to visually screenshot it, so a look in an actual browser is still worth doing before you consider it final.

### M-Pesa go-live
1. Register/sign in at the [Safaricom Daraja portal](https://developer.safaricom.co.ke) and create an app to get sandbox Consumer Key/Secret (already usable as-is for testing against shortcode `174379`).
2. Separately apply for a production Paybill/Till (Lipa Na M-Pesa Online) shortcode + passkey — this has real-world approval lead time, worth starting early.
3. Fill in real values in `config/mpesa.php` (`consumer_key`, `consumer_secret`, `passkey`, and `callback_base_url` — must be a real public HTTPS URL, Safaricom cannot call `localhost`). Flip `env` to `production` when going live.

## Local setup (Windows)

Requirements: PHP 8.1+ with `pdo_mysql`, `gd`, `fileinfo`, `mbstring` extensions; a MySQL/MariaDB server (this project was developed against the MySQL bundled with XAMPP at `C:\xampp\mysql`).

1. **Start MySQL** (if using XAMPP's bundled server and it isn't already running):
   ```
   "C:\xampp\mysql\bin\mysqld.exe" --datadir="C:\xampp\mysql\data"
   ```
2. **Create the database and import the schema + content seed**:
   ```
   "C:\xampp\mysql\bin\mysql.exe" -u root -e "CREATE DATABASE IF NOT EXISTS mascardi_lifestyle CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   "C:\xampp\mysql\bin\mysql.exe" -u root mascardi_lifestyle < database\schema.sql
   "C:\xampp\mysql\bin\mysql.exe" -u root mascardi_lifestyle < database\seed.sql
   ```
   > Note: an unrelated pre-existing database named `mascardi_db` (a separate car dealership/workshop system) also lives on this MySQL instance — this project intentionally uses the distinct name `mascardi_lifestyle` and never touches `mascardi_db`.
3. **Create your first admin login** (not included in `seed.sql` on purpose — no real credentials are committed to this repo):
   ```
   php -r "echo password_hash('YourStrongPassword', PASSWORD_DEFAULT), PHP_EOL;"
   ```
   Copy `database/seed_admin.example.sql` → `database/seed_admin.sql` (gitignored), paste your generated hash and email in, then run:
   ```
   "C:\xampp\mysql\bin\mysql.exe" -u root mascardi_lifestyle < database\seed_admin.sql
   ```
4. **Configure**: copy `config/config.example.php` → `config/config.php` and `config/mpesa.example.php` → `config/mpesa.php` (both gitignored), and fill in your local DB credentials (defaults: root/no password, `mascardi_lifestyle` DB).
5. **Run the dev server**:
   ```
   php -S localhost:8000 -t public
   ```
6. Visit `http://localhost:8000/` for the public site, `http://localhost:8000/admin/index.php?module=auth&action=login` for the admin panel (sign in with the email/password you just created).

## Project layout

- `app/` — PHP source (Core framework classes, Models, Controllers, Services). Never web-exposed.
- `config/` — `config.php` / `mpesa.php` hold real credentials and are gitignored; `.example.php` versions are the committed templates.
- `database/schema.sql`, `database/seed.sql` — source of truth for the DB (schema + non-sensitive content). `database/seed_admin.sql` (gitignored, from the `.example` template) creates your first admin login.
- `public/` — the web root. Maps to `public_html` on cPanel.
- `resources/views/` — plain-PHP templates (`layouts/`, `partials/`, `site/`, `admin/`).
- `storage/` — logs and cache, PHP-writable, never web-exposed.

## cPanel deployment

Deployment runbook (web-root mapping, AutoSSL, production M-Pesa credential swap) will be written in full during Phase 5. In short: only the contents of `public/` go into `public_html`; `app/`, `config/`, `database/`, `storage/`, `vendor/` are uploaded one level above it so they're never web-reachable.

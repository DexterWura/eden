# Eden — Startup Directory

Eden is a curated startup directory: first-visit install, public site, and founder/admin dashboards (Laravel-based). All project documentation is in this file.

---

## Features currently available

- **First-visit install wizard** (steps 1–5): requirements check (PHP 8.1+, extensions, writable dirs, `core/vendor`), database config, app name/URL + admin user, run migrations and create admin, completion with links to site and admin.
- **Public site (Laravel-driven):**
  - **Home:** hero, “launching today” strip, product of the day (top 5 by upvotes), category filters, startup list (from DB), debounced search, CTA, newsletter form (subscribe).
  - **Pages:** About, Contact, Submit startup (form posts to DB), Categories (from DB), Launching today (DB), single Startup by slug (dynamic).
  - **Global:** header/nav, mobile drawer, login/signup modals (working auth); footer; clean URLs.
- **Auth:** User registration and login (session); `/startup` (founder dashboard) protected by `auth` middleware; `/backoffice` (admin) protected by `admin` middleware. Logout and redirects (e.g. intended URL after login) supported.
- **Data:** Startups table (migration + seed of 3 sample startups); categories derived from startups; home, launching-today, categories, and single startup page are DB-driven. Submit startup form creates new startups (slug, category, launch date, etc.).
- **Newsletter:** Subscribers table; POST `/subscribe` stores email; forms on home and launching-today.
- **Admin backoffice:** Dashboard plus **Migrations** page at `/backoffice/migrations`: list all migrations (ran / pending / modified), run pending, rerun modified, rollback, download SQL backup. Admin-only; tables created on demand.
- **Dashboards (UI):**
  - **Founder dashboard** (`/startup`): KPIs, upvotes-over-time card, recently accessed placeholder; responsive layout and topbar.
  - **Admin dashboard** (`/backoffice`): Same layout; sidebar link to Migrations.
- **UI & responsiveness:** Dashboard and public site use responsive CSS (breakpoints at 1024px, 768px, 640px, 380px). Dashboard: topbar wraps cleanly (search full-width on small screens), sidebar becomes overlay with hamburger in topbar; KPI grid and cards stack on small screens; chart placeholder has clear min-height. Public: wrap padding, hero, filters/pills, startup cards, category list, forms, and startup detail page adapt to all screen sizes.
- **Assets:** CSS in `core/public/css/` (main.css, dashboard.css); `.htaccess` serves `core/public` for existing files.
- **Tech:** PHP 8.1+, Laravel (core in `core/`), document root at project root; no `.env` → redirect to `/install/`.

---

## Deployment (vendor)

If you deploy without `core/vendor` (e.g. it is in `.gitignore`):

- Run `composer install --no-dev` inside `core/` on the server, **or**
- Copy the full `core/vendor` directory from a machine that has it (e.g. after running `composer install` locally).

The install wizard step 1 requires `core/vendor/autoload.php` to be present before you can continue.

### Production release checklist

Run these commands from `core/` after deploying code and before switching traffic:

```shell
php artisan migrate --force
php artisan optimize
php artisan sitemap:generate
```

Verify that `/sitemap.xml` and `/robots.txt` return HTTP 200. Eden also serves both dynamically if a generated public file is missing. `/ads.txt` remains HTTP 404 until a real `ca-pub-*` AdSense account is configured, preventing placeholder publisher records.

Before enabling AdSense, enrich and review thin startup profiles in **Backoffice → Startups → Needs enrichment**, publish original editorial/category content, verify the consent choices, and confirm that direct ads and Google ads do not occupy the same placement.

---

## Roadmap

**Done**

1. **Auth** — Registration and login; founder dashboard (`/startup`) and admin (`/backoffice`) protected by middleware.
2. **Startups data model** — Migration + seed (3 sample startups); “Submit your startup” saves to DB; single startup page by slug.
3. **Public listings** — Home, Launching today, and Categories driven by DB; category filter on home; single startup page by slug.
4. **Newsletter** — Subscribers table and POST `/subscribe`; forms wired on home and launching-today.
5. **Admin migrations UI** — `/backoffice/migrations`: list migrations (ran/pending/modified), run pending, rerun modified, rollback, download SQL.
6. **UI & responsiveness** — Dashboard and public site responsive across breakpoints; dashboard topbar/cards/chart and public pages (hero, filters, cards, forms, startup detail) fixed for all screens.

**Planned (in order)**

1. **Upvotes** — Store upvotes (startup_id + user/session); show counts and “Product of the day” from data.
2. **Contact form** — Backend handler and email (or queue).
3. **Founder dashboard** — Real KPIs and “my startup” linked to logged-in user’s startup(s).
4. **Admin dashboard** — Real startup list, moderation queue, and actions (approve/reject).
5. **Privacy/Terms** — Static or editable pages and footer links.

Optional later: server-side search, email verification, password reset, roles (admin vs founder).

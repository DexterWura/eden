# Eden — Startup Directory

Eden is a curated startup directory: first-visit install, public site, and founder/admin dashboards (Laravel-based).

---

## Features currently available

- **First-visit install wizard** (steps 1–5): requirements check (PHP 8.1+, extensions, writable dirs, `core/vendor`), database config, app name/URL + admin user, run migrations and create admin, completion with links to site and admin.
- **Public site (Laravel-driven):**
  - **Home:** hero, “launching today” strip, product of the day, category filters, startup list, CTA, newsletter block.
  - **Pages:** About, Contact, Submit startup (forms), Categories, Launching today (list), single Startup (Nexus Pay placeholder).
  - **Global:** header/nav, mobile drawer, login/signup modals (UI only), footer; links use clean URLs (no `.html`).
- **Dashboards (UI only):**
  - **Founder dashboard:** KPIs (upvotes, profile views, link clicks, product of the day), upvotes-over-time card, “recently accessed” placeholder.
  - **Admin dashboard:** KPIs (total startups, new this week, launching today, pending review), recent startups table, moderation queue placeholder.
- **Assets:** CSS from `core/public/css/` (main.css, dashboard.css); `.htaccess` serves `core/public` for existing files.
- **Tech:** PHP 8.1+, Laravel (core in `core/`), document root at project root; no `.env` → redirect to `/install/`.

---

## Deployment (vendor)

If you deploy without `core/vendor` (e.g. it is in `.gitignore`):

- Run `composer install --no-dev` inside `core/` on the server, **or**
- Copy the full `core/vendor` directory from a machine that has it (e.g. after running `composer install` locally or from the Flippa clone).

The install wizard step 1 requires `core/vendor/autoload.php` to be present before you can continue.

---

## Features not implemented (planned)

- **Auth:** Login/signup modals and “Log in”/“Sign up” do not persist or authenticate; no session-based auth or registration.
- **Data:** All startup/category data is static (views). No DB-backed startups, categories, or “launching today”; no search.
- **Forms:** Contact, Submit startup, and newsletter do not submit to backend or send email.
- **Founder dashboard:** No real “my startup”, upvotes, or reports; no backend.
- **Admin dashboard:** No real startup list, moderation queue, or settings; no admin auth.
- **Single startup page:** Only one placeholder (Nexus Pay); no dynamic slug or DB-driven startup pages.
- **Categories:** Category cards use `href="#"`; no category detail or filtered listing.
- **Upvotes:** Upvote buttons are client-only (no persistence).
- **Privacy/Terms:** Footer “Privacy” and “Terms” are placeholders.

---

## Roadmap

Implement in this order as we build:

1. **Auth** — Registration and login (session or Laravel auth), then protect founder/admin dashboards.
2. **Startups data model** — Migrations and models for startups (and categories if needed); CRUD for admin; “Submit your startup” saves to DB.
3. **Public listings** — Home, Launching today, and Categories driven by DB; single startup page by slug/id.
4. **Upvotes** — Store upvotes (e.g. startup_id + user/session); show counts and “Product of the day” from data.
5. **Contact form** — Backend handler and email (or queue).
6. **Newsletter** — Store emails and/or integrate a provider.
7. **Founder dashboard** — Real KPIs and “my startup” linked to logged-in user’s startup(s).
8. **Admin dashboard** — Real startup list, moderation queue, and actions (approve/reject).
9. **Privacy/Terms** — Static or editable pages and footer links.

Optional later: search, filters, email verification, password reset, roles (admin vs founder).

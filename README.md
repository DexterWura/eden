# Eden – Tech Startup Directory

Laravel-based directory for tech startups, SaaS, and online businesses. Theme: "The Startup Garden" — listings grow from seedlings to flourishing trees.

**Stack:** Laravel (PHP 8.2+), MySQL, Blade, vanilla CSS/JS.

---

## Features

### Implemented

- **Startup directory**
  - Public listing at `/startups` with search, category/status/for-sale filters.
  - Startup pages: `/startups/{slug}` with description, category, badges (New, Featured, Verified, status), founder, tags, MRR/ARR, for-sale link.
  - Admin can add/edit/delete startups; approve/reject submissions.
  - Status: seedling, sapling, flourishing, wilted (dormant when URL fails health check).
- **Submission workflow**
  - Visitors submit at "Submit startup"; admin approves/rejects from Submissions.
- **Claim & verification**
  - Claim via meta tag: `<meta name="eden-verification" content="…">` on the startup’s site; verify from the claim flow.
  - Only approved startups can be claimed.
- **Voting**
  - Logged-in users can upvote startups (count shown on listing and detail).
- **Categories**
  - Admin manages categories; startups filtered by category; category pages at `/category/{slug}`.
- **Admin dashboard (Google Analytics–style)**
  - Fixed top bar (brand, View site, user, Sign out) and sidebar (Reports snapshot, Startups, Submissions, Categories, Ads, Settings, Migrations, System Health, Pruning).
  - Reports snapshot: total startups, pending submissions, claimed/unclaimed, recent submissions, most viewed, top MRR.
- **Themes (public site)**
  - Admin can choose site theme in **Settings → Site theme**. Default theme: **Basic**. Each theme has its own CSS in `public/css/themes/{name}.css`. Add themes in `config/themes.php` and a matching CSS file.
- **Ad manager**
  - Slots: above fold, in-feed, sidebar, in-content. Types: AdSense, ZimAdsense, custom HTML. Global AdSense client ID in Settings. Fallback to AdSense when a unit is inactive/expired.
- **Settings**
  - Site name, App URL, timezone, logo (upload), AdSense client ID, **Site theme**.
- **UI migrations**
  - Admin → Migrations: list pending/modified migrations, run migrations from the dashboard (no SSH).
- **Pruning**
  - Find startups by URL pattern or empty description; bulk delete.
- **System health**
  - Last run times for health check, cleanup, reminder, newsletter. Buttons to run health check, cleanup, reminder, newsletter manually. Cron instructions shown.
- **Automation (scheduled commands)**
  - Health check (startup URL ping → wilted if offline).
  - Cleanup (unverified seedlings older than 30 days).
  - Reminder (email owners to update).
  - Weekly newsletter (top startups to subscribers); footer signup form; throttle on subscribe.
- **SEO**
  - Canonical URLs, meta description on startup pages, JSON-LD on startup pages. Description &lt;300 words prompt for owners.
- **Newsletter**
  - Footer form to subscribe; stored subscribers; weekly newsletter command.
- **Styling**
  - Public site: theme-based CSS (default Basic). Admin: separate CSS; GA-inspired layout and components. No inline styles; all in `public/css/` (app theme files and `admin.css`).

### Not done / Planned

- **Tailwind CSS / Alpine.js** – Spec mentioned Tailwind and Alpine; current UI uses plain CSS and minimal JS.
- **Spatie Media Library / WebP** – Not integrated; no automatic image conversion.
- **Redis** – Caching is file-based (and DB settings); Redis not required.
- **Comments / reviews** – Not implemented.
- **DNS TXT or file upload verification** – Only meta-tag verification is implemented.
- **Auto-approval** of submissions based on website check – Not implemented.
- **Featured listings (paid)** – No paid placement or payment flow.
- **Premium analytics** – No visitor/interaction analytics beyond view count and basic dashboard stats.
- **Sponsored / advertisement options** – Ads are slot-based; no “sponsored” labels or affiliate API.
- **Verified investors section** – Not implemented.
- **Multiple admins per startup / OAuth (e.g. Google Search Console)** – Single owner per startup; no OAuth claim.
- **Blog/spotlight section** – Not implemented.
- **API access** – No public API.
- **Social sharing buttons** – Not implemented.
- **Seasonal / garden animations** – Only basic hover and card styling; no seasonal or advanced animations.

---

## Installation

### Requirements

- PHP 8.2+
- Composer
- MySQL 5.7+ or MariaDB
- Web server (Apache or Nginx) with document root set to `public/`

### Local (XAMPP / Laragon)

1. Clone or upload the project.
2. Copy `.env.example` to `.env` (or use the web installer to create it).
3. Run: `composer install --no-dev` (or `composer install` for development).
4. Run: `php artisan key:generate` (if not using the web installer).
5. Create a MySQL database and set `DB_*` in `.env`.
6. Either:
   - **Web installer:** Open `http://your-local-url/install` in the browser and complete the form (database, site name, admin account, optional logo URL). The installer runs migrations and disables itself.
   - **CLI:** Run `php artisan migrate --force`, then create an admin user manually or via a seeder.
7. Ensure `storage/` and `bootstrap/cache/` are writable.
8. Point the document root to the `public/` directory.

### Live server / VPS

1. Upload the project (e.g. via Git or FTP).
2. Set the web server document root to `public/`.
3. If you have SSH: run `composer install --no-dev` and `php artisan key:generate`. Otherwise use the **web installer** at `https://your-domain.com/install` and fill in database, site name, logo, and admin account. The installer runs migrations and sets `EDEN_INSTALLED=true`.
4. Make `storage/` and `bootstrap/cache/` writable:  
   `chmod -R 775 storage bootstrap/cache`

### Shared hosting (FTP only)

1. Upload all files. Set the domain’s document root to the `public` folder (e.g. “Document root” pointing to `eden/public` or equivalent).
2. Ensure `storage/` and `bootstrap/cache/` are writable (e.g. chmod 775 via file manager).
3. Open `https://your-domain.com/install` and complete the installer (database, site name, admin). It writes `.env` and runs migrations. No SSH required.

### After install

- **Admin:** Log in at `/login` with the admin account, then go to `/admin` (or use “Admin” in the main nav when logged in as admin).
- **Migrations:** In Admin → Migrations you can see pending migrations and run them with one click.
- **Cron (optional):** For health check, cleanup, reminder emails, and newsletter, add a cron job:  
  `* * * * * php /path/to/your/project/artisan schedule:run`

### Troubleshooting

- **500 error:** Check that `storage/` and `bootstrap/cache/` are writable and that `APP_KEY` is set in `.env`.
- **Install page not loading:** Ensure `EDEN_INSTALLED` is not set to `true` in `.env` before you’ve finished installing.
- **Database connection error:** Verify `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` in `.env`.

---

## Config & themes

- **Themes:** Configured in `config/themes.php`. Default theme is `basic`; CSS lives in `public/css/themes/basic.css`. To add a theme, add an entry in `config/themes.php` and create `public/css/themes/{key}.css`.
- **Site settings:** Admin → Settings (site name, URL, timezone, logo, AdSense client ID, theme). No need to edit `.env` for these.

---

## Development reference

- **Database:** Users, Startups, Categories, Claims, Votes, Ads, Settings, NewsletterSubscriber, GrowthLog (and migrations). Status lifecycle: seedling → sapling → flourishing; wilted when URL is down.
- **Key routes:** `/`, `/startups`, `/startups/create`, `/startups/{slug}`, `/category/{slug}`, `/claim/{slug}`, `/login`, `/register`, `/admin` (and admin sub-routes), `/install` (until installed).

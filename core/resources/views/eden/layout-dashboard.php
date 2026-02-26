<!DOCTYPE html>
<html lang="en" class="dashboard">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if (function_exists('csrf_token')): ?><meta name="csrf-token" content="<?= e(csrf_token()) ?>"><?php endif; ?>
  <title><?= e($title ?? 'Dashboard') ?> — <?= e($dashboardLogo ?? 'Eden') ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?>">
  <link rel="stylesheet" href="<?= e(asset('css/dashboard.css')) ?>">
</head>
<body class="dashboard">
  <div class="dash-layout" id="dashLayout">
    <div class="dash-sidebar-backdrop" id="dashSidebarBackdrop"></div>
    <aside class="dash-sidebar" id="dashSidebar">
      <button type="button" class="dash-sidebar-expand-toggle" id="dashSidebarExpandToggle" aria-label="Expand sidebar" title="Expand sidebar">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
      <?php if (($sidebar ?? '') === 'admin'): ?>
        <a href="<?= e(url('/backoffice')) ?>" class="<?= in_array($activeNav ?? '', ['migrations', 'startups', 'users', 'categories', 'blog', 'contact-messages', 'subscribers', 'reports', 'scheduled-tasks', 'seo', 'about', 'settings'], true) ? '' : 'active' ?>" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i><span class="dash-sidebar-label">Home</span></a>
        <a href="<?= e(url('/backoffice/migrations')) ?>" class="<?= ($activeNav ?? '') === 'migrations' ? 'active' : '' ?>" aria-label="Migrations" title="Migrations"><i class="fa-solid fa-database"></i><span class="dash-sidebar-label">Migrations</span></a>
        <a href="<?= e(url('/backoffice/startups')) ?>" class="<?= ($activeNav ?? '') === 'startups' ? 'active' : '' ?>" aria-label="Startups" title="Startups"><i class="fa-solid fa-rocket"></i><span class="dash-sidebar-label">Startups</span></a>
        <a href="<?= e(url('/backoffice/users')) ?>" class="<?= ($activeNav ?? '') === 'users' ? 'active' : '' ?>" aria-label="Users" title="Users"><i class="fa-solid fa-user"></i><span class="dash-sidebar-label">Users</span></a>
        <a href="<?= e(url('/backoffice/categories')) ?>" class="<?= ($activeNav ?? '') === 'categories' ? 'active' : '' ?>" aria-label="Categories" title="Categories"><i class="fa-solid fa-folder-tree"></i><span class="dash-sidebar-label">Categories</span></a>
        <a href="<?= e(url('/backoffice/blog')) ?>" class="<?= ($activeNav ?? '') === 'blog' ? 'active' : '' ?>" aria-label="Blog" title="Blog"><i class="fa-solid fa-pen-nib"></i><span class="dash-sidebar-label">Blog</span></a>
        <a href="<?= e(url('/backoffice/contact-messages')) ?>" class="<?= ($activeNav ?? '') === 'contact-messages' ? 'active' : '' ?>" aria-label="Contact messages" title="Contact messages"><i class="fa-solid fa-message"></i><span class="dash-sidebar-label">Messages</span></a>
        <a href="<?= e(url('/backoffice/subscribers')) ?>" class="<?= ($activeNav ?? '') === 'subscribers' ? 'active' : '' ?>" aria-label="Subscribers" title="Subscribers"><i class="fa-solid fa-envelope"></i><span class="dash-sidebar-label">Subscribers</span></a>
        <a href="<?= e(url('/backoffice/reports')) ?>" class="<?= ($activeNav ?? '') === 'reports' ? 'active' : '' ?>" aria-label="Reports" title="Reports"><i class="fa-solid fa-chart-line"></i><span class="dash-sidebar-label">Reports</span></a>
        <a href="<?= e(url('/backoffice/scheduled-tasks')) ?>" class="<?= ($activeNav ?? '') === 'scheduled-tasks' ? 'active' : '' ?>" aria-label="Scheduled tasks" title="Scheduled tasks"><i class="fa-solid fa-clock-rotate-left"></i><span class="dash-sidebar-label">Scheduled</span></a>
        <a href="<?= e(url('/backoffice/seo')) ?>" class="<?= ($activeNav ?? '') === 'seo' ? 'active' : '' ?>" aria-label="SEO" title="SEO"><i class="fa-solid fa-magnifying-glass-chart"></i><span class="dash-sidebar-label">SEO</span></a>
        <a href="<?= e(url('/backoffice/about')) ?>" class="<?= ($activeNav ?? '') === 'about' ? 'active' : '' ?>" aria-label="About page" title="About page"><i class="fa-solid fa-circle-info"></i><span class="dash-sidebar-label">About</span></a>
        <a href="<?= e(url('/backoffice/settings')) ?>" class="<?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>" aria-label="Settings" title="Settings"><i class="fa-solid fa-gear"></i><span class="dash-sidebar-label">Settings</span></a>
      <?php else: ?>
        <a href="<?= e(url('/founder')) ?>" class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i><span class="dash-sidebar-label">Home</span></a>
        <a href="<?= e(url('/founder/startups')) ?>" class="<?= ($activeNav ?? '') === 'startups' ? 'active' : '' ?>" aria-label="My startup" title="My startups"><i class="fa-solid fa-building-user"></i><span class="dash-sidebar-label">Startups</span></a>
        <a href="<?= e(url('/founder/upvotes')) ?>" class="<?= ($activeNav ?? '') === 'upvotes' ? 'active' : '' ?>" aria-label="Upvotes" title="Upvotes"><i class="fa-solid fa-arrow-up"></i><span class="dash-sidebar-label">Upvotes</span></a>
        <a href="<?= e(url('/founder/revenue-api')) ?>" class="<?= ($activeNav ?? '') === 'revenue-api' ? 'active' : '' ?>" aria-label="Revenue API" title="Revenue API"><i class="fa-solid fa-code"></i><span class="dash-sidebar-label">Revenue API</span></a>
        <a href="<?= e(url('/founder/settings')) ?>" class="<?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>" aria-label="Settings" title="Settings"><i class="fa-solid fa-gear"></i><span class="dash-sidebar-label">Settings</span></a>
      <?php endif; ?>
      <div class="dash-nav-bottom">
        <a href="<?= e(url('/')) ?>" aria-label="Back to Eden" title="Back to site"><i class="fa-solid fa-arrow-left"></i><span class="dash-sidebar-label">Back to site</span></a>
      </div>
    </aside>
    <div class="dash-body">
      <header class="dash-topbar">
        <button type="button" class="dash-sidebar-toggle" id="dashSidebarToggle" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
        <div class="dash-topbar-left">
          <a href="<?= e($sidebar === 'admin' ? url('/backoffice') : url('/founder')) ?>" class="dash-logo">
            <span class="dash-logo-dots"><span></span><span></span><span></span><span></span></span>
            <?= e($dashboardLogo ?? 'Eden') ?>
          </a>
          <?= $dashboardTopbar ?? '' ?>
        </div>
        <div class="dash-search-wrap" style="position: relative;">
          <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--d-text-secondary); pointer-events: none;"></i>
          <input type="search" class="dash-search" placeholder="<?= e($searchPlaceholder ?? 'Search') ?>" aria-label="Search">
        </div>
        <div class="dash-topbar-right">
          <a href="<?= e(url('/contact')) ?>" aria-label="Help" title="Contact / Help"><i class="fa-solid fa-circle-question" aria-hidden="true"></i></a>
          <?php if (($sidebar ?? '') === 'admin' && function_exists('route') && auth()->guard('admin')->check()): ?>
            <a href="<?= e(route('admin.logout')) ?>" class="dash-logout" aria-label="Log out" title="Log out"><i class="fa-solid fa-arrow-right-from-bracket"></i></a>
          <?php endif; ?>
          <?php if (($sidebar ?? '') === 'founder' && auth()->check()): ?>
            <form action="<?= e(route('logout')) ?>" method="POST" style="display: inline;">
              <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
              <button type="submit" class="dash-logout" style="background: none; border: none; padding: 0; cursor: pointer; color: inherit; font-size: inherit;" aria-label="Log out" title="Log out"><i class="fa-solid fa-arrow-right-from-bracket"></i></button>
            </form>
          <?php endif; ?>
          <div class="dash-avatar" title="<?= e($avatarTitle ?? 'Account') ?>"><?= e($avatarLetter ?? '?') ?></div>
        </div>
      </header>
      <main class="dash-main">
        <div class="dash-content">
          <?= $content ?? '' ?>
        </div>
      </main>
    </div>
  </div>
  <script>
    (function() {
      var toggle = document.getElementById('dashSidebarToggle');
      var sidebar = document.getElementById('dashSidebar');
      var backdrop = document.getElementById('dashSidebarBackdrop');
      var layout = document.getElementById('dashLayout');
      var expandBtn = document.getElementById('dashSidebarExpandToggle');
      var STORAGE_KEY = 'eden-dash-sidebar-expanded';

      if (toggle && sidebar) {
        toggle.addEventListener('click', function() {
          sidebar.classList.toggle('is-open');
          if (backdrop) backdrop.classList.toggle('is-open');
        });
        if (backdrop) {
          backdrop.addEventListener('click', function() {
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('is-open');
          });
        }
      }

      function setExpanded(expanded) {
        if (!layout) return;
        if (expanded) {
          layout.classList.add('dash-sidebar-expanded');
          if (expandBtn) {
            expandBtn.setAttribute('aria-label', 'Collapse sidebar');
            expandBtn.setAttribute('title', 'Collapse sidebar');
            var icon = expandBtn.querySelector('i');
            if (icon) { icon.className = 'fa-solid fa-chevron-left'; }
          }
          try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
        } else {
          layout.classList.remove('dash-sidebar-expanded');
          if (expandBtn) {
            expandBtn.setAttribute('aria-label', 'Expand sidebar');
            expandBtn.setAttribute('title', 'Expand sidebar');
            var icon = expandBtn.querySelector('i');
            if (icon) { icon.className = 'fa-solid fa-chevron-right'; }
          }
          try { localStorage.setItem(STORAGE_KEY, '0'); } catch (e) {}
        }
      }

      if (expandBtn && layout) {
        expandBtn.addEventListener('click', function() {
          setExpanded(!layout.classList.contains('dash-sidebar-expanded'));
        });
        try {
          if (localStorage.getItem(STORAGE_KEY) === '1') setExpanded(true);
        } catch (e) {}
      }
    })();
  </script>
  <?= $scriptDeps ?? '' ?>
  <?= $notifyPartial ?? '' ?>
  <?= $scripts ?? '' ?>
</body>
</html>

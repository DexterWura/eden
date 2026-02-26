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
  <div class="dash-layout">
    <div class="dash-sidebar-backdrop" id="dashSidebarBackdrop"></div>
    <aside class="dash-sidebar" id="dashSidebar">
      <?php if (($sidebar ?? '') === 'admin'): ?>
        <a href="<?= e(url('/backoffice')) ?>" class="<?= in_array($activeNav ?? '', ['migrations', 'startups', 'users', 'categories', 'subscribers', 'reports', 'settings'], true) ? '' : 'active' ?>" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i></a>
        <a href="<?= e(url('/backoffice/migrations')) ?>" class="<?= ($activeNav ?? '') === 'migrations' ? 'active' : '' ?>" aria-label="Migrations" title="Migrations"><i class="fa-solid fa-database"></i></a>
        <a href="<?= e(url('/backoffice/startups')) ?>" class="<?= ($activeNav ?? '') === 'startups' ? 'active' : '' ?>" aria-label="Startups" title="Startups"><i class="fa-solid fa-rocket"></i></a>
        <a href="<?= e(url('/backoffice/users')) ?>" class="<?= ($activeNav ?? '') === 'users' ? 'active' : '' ?>" aria-label="Users" title="Users"><i class="fa-solid fa-user"></i></a>
        <a href="<?= e(url('/backoffice/categories')) ?>" class="<?= ($activeNav ?? '') === 'categories' ? 'active' : '' ?>" aria-label="Categories" title="Categories"><i class="fa-solid fa-folder-tree"></i></a>
        <a href="<?= e(url('/backoffice/subscribers')) ?>" class="<?= ($activeNav ?? '') === 'subscribers' ? 'active' : '' ?>" aria-label="Subscribers" title="Subscribers"><i class="fa-solid fa-envelope"></i></a>
        <a href="<?= e(url('/backoffice/reports')) ?>" class="<?= ($activeNav ?? '') === 'reports' ? 'active' : '' ?>" aria-label="Reports" title="Reports"><i class="fa-solid fa-chart-line"></i></a>
        <a href="<?= e(url('/backoffice/settings')) ?>" class="<?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>" aria-label="Settings" title="Settings"><i class="fa-solid fa-gear"></i></a>
      <?php else: ?>
        <a href="<?= e(url('/founder')) ?>" class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i></a>
        <a href="<?= e(url('/founder/startups')) ?>" class="<?= ($activeNav ?? '') === 'startups' ? 'active' : '' ?>" aria-label="My startup" title="My startups"><i class="fa-solid fa-building-user"></i></a>
        <a href="<?= e(url('/founder/upvotes')) ?>" class="<?= ($activeNav ?? '') === 'upvotes' ? 'active' : '' ?>" aria-label="Upvotes" title="Upvotes"><i class="fa-solid fa-arrow-up"></i></a>
        <a href="<?= e(url('/founder/settings')) ?>" class="<?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>" aria-label="Settings" title="Settings"><i class="fa-solid fa-gear"></i></a>
      <?php endif; ?>
      <div class="dash-nav-bottom">
        <a href="<?= e(url('/')) ?>" aria-label="Back to Eden" title="Back to site"><i class="fa-solid fa-arrow-left"></i></a>
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
    })();
  </script>
  <?= $scriptDeps ?? '' ?>
  <?= $notifyPartial ?? '' ?>
  <?= $scripts ?? '' ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en" class="dashboard">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($title ?? 'Dashboard') ?> — Eden</title>
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
    <button type="button" class="dash-sidebar-toggle" id="dashSidebarToggle" aria-label="Open menu"><i class="fa-solid fa-bars"></i></button>
    <aside class="dash-sidebar" id="dashSidebar">
      <?php if (($sidebar ?? '') === 'admin'): ?>
        <a href="<?= e(url('/admin-dashboard')) ?>" class="active" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i></a>
        <a href="#" aria-label="Startups" title="Startups"><i class="fa-solid fa-rocket"></i></a>
        <a href="#" aria-label="Users" title="Users"><i class="fa-solid fa-user"></i></a>
        <a href="#" aria-label="Reports" title="Reports"><i class="fa-solid fa-chart-line"></i></a>
        <a href="#" aria-label="Settings" title="Settings"><i class="fa-solid fa-gear"></i></a>
      <?php else: ?>
        <a href="<?= e(url('/founder-dashboard')) ?>" class="active" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i></a>
        <a href="#" aria-label="My startup" title="My startup"><i class="fa-solid fa-building-user"></i></a>
        <a href="#" aria-label="Upvotes" title="Upvotes"><i class="fa-solid fa-arrow-up"></i></a>
        <a href="#" aria-label="Settings" title="Settings"><i class="fa-solid fa-gear"></i></a>
      <?php endif; ?>
      <div class="dash-nav-bottom">
        <a href="<?= e(url('/')) ?>" aria-label="Back to Eden" title="Back to site"><i class="fa-solid fa-arrow-left"></i></a>
      </div>
    </aside>
    <div class="dash-body">
      <header class="dash-topbar">
        <div class="dash-topbar-left">
          <a href="<?= e($sidebar === 'admin' ? url('/admin-dashboard') : url('/founder-dashboard')) ?>" class="dash-logo">
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
          <a href="#" aria-label="Help">?</a>
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
  <?= $scripts ?? '' ?>
</body>
</html>

<!DOCTYPE html>
<html lang="en" class="dashboard">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if (function_exists('csrf_token')): ?><meta name="csrf-token" content="<?= e(csrf_token()) ?>"><?php endif; ?>
  <title><?= e($title ?? 'Dashboard') ?> — <?= e($dashboardLogo ?? 'Eden') ?></title>
  <link rel="icon" type="image/png" href="<?= e(asset('images/favicon.png')) ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <?php
    $mainCssPath = public_path('css/main.css');
    $mainCssVersion = file_exists($mainCssPath) ? substr(md5_file($mainCssPath), 0, 12) : '';
    $dashCssPath = public_path('css/dashboard.css');
    $dashCssVersion = file_exists($dashCssPath) ? substr(md5_file($dashCssPath), 0, 12) : '';
  ?>
  <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?><?= $mainCssVersion ? '?v=' . $mainCssVersion : '' ?>">
  <link rel="stylesheet" href="<?= e(asset('css/dashboard.css')) ?><?= $dashCssVersion ? '?v=' . $dashCssVersion : '' ?>">
</head>
<body class="dashboard">
  <div class="dash-layout" id="dashLayout">
    <div class="dash-sidebar-backdrop" id="dashSidebarBackdrop"></div>
    <aside class="dash-sidebar" id="dashSidebar">
      <button type="button" class="dash-sidebar-expand-toggle" id="dashSidebarExpandToggle" aria-label="Expand sidebar" title="Expand sidebar">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
      <?php if (($sidebar ?? '') === 'admin'): ?>
        <a href="<?= e(url('/backoffice')) ?>" class="<?= in_array($activeNav ?? '', ['migrations', 'startups', 'startup-websites', 'users', 'categories', 'blog', 'contact-messages', 'startup-reports', 'subscribers', 'reports', 'scheduled-tasks', 'seo', 'about', 'settings'], true) ? '' : 'active' ?>" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i><span class="dash-sidebar-label">Home</span></a>
        <a href="<?= e(url('/backoffice/migrations')) ?>" class="<?= ($activeNav ?? '') === 'migrations' ? 'active' : '' ?>" aria-label="Migrations" title="Migrations"><i class="fa-solid fa-database"></i><span class="dash-sidebar-label">Migrations</span></a>
        <a href="<?= e(url('/backoffice/startups')) ?>" class="<?= ($activeNav ?? '') === 'startups' ? 'active' : '' ?>" aria-label="Startups" title="Startups"><i class="fa-solid fa-rocket"></i><span class="dash-sidebar-label">Startups</span><?php if (($adminPendingStartupsBadge ?? 0) > 0): ?><span class="dash-nav-badge" aria-label="<?= (int)($adminPendingStartupsBadge ?? 0) ?> pending"><?= (int)($adminPendingStartupsBadge) ?></span><?php endif; ?></a>
        <a href="<?= e(url('/backoffice/startup-websites')) ?>" class="<?= ($activeNav ?? '') === 'startup-websites' ? 'active' : '' ?>" aria-label="Website health" title="Startup website health"><i class="fa-solid fa-globe"></i><span class="dash-sidebar-label">Website health</span></a>
        <a href="<?= e(url('/backoffice/users')) ?>" class="<?= ($activeNav ?? '') === 'users' ? 'active' : '' ?>" aria-label="Users" title="Users"><i class="fa-solid fa-user"></i><span class="dash-sidebar-label">Users</span></a>
        <a href="<?= e(url('/backoffice/categories')) ?>" class="<?= ($activeNav ?? '') === 'categories' ? 'active' : '' ?>" aria-label="Categories" title="Categories"><i class="fa-solid fa-folder-tree"></i><span class="dash-sidebar-label">Categories</span></a>
        <a href="<?= e(url('/backoffice/blog')) ?>" class="<?= ($activeNav ?? '') === 'blog' ? 'active' : '' ?>" aria-label="Blog" title="Blog"><i class="fa-solid fa-pen-nib"></i><span class="dash-sidebar-label">Blog</span></a>
        <a href="<?= e(url('/backoffice/contact-messages')) ?>" class="<?= ($activeNav ?? '') === 'contact-messages' ? 'active' : '' ?>" aria-label="Contact messages" title="Contact messages"><i class="fa-solid fa-message"></i><span class="dash-sidebar-label">Messages</span><?php if (($adminUnseenMessagesBadge ?? 0) > 0): ?><span class="dash-nav-badge" aria-label="<?= (int)($adminUnseenMessagesBadge ?? 0) ?> unseen"><?= (int)($adminUnseenMessagesBadge) ?></span><?php endif; ?></a>
        <a href="<?= e(url('/backoffice/startup-reports')) ?>" class="<?= ($activeNav ?? '') === 'startup-reports' ? 'active' : '' ?>" aria-label="Listing reports" title="Listing reports"><i class="fa-solid fa-flag"></i><span class="dash-sidebar-label">Flags</span><?php if (($adminPendingListingReportsBadge ?? 0) > 0): ?><span class="dash-nav-badge" aria-label="<?= (int)($adminPendingListingReportsBadge ?? 0) ?> pending"><?= (int)($adminPendingListingReportsBadge) ?></span><?php endif; ?></a>
        <a href="<?= e(url('/backoffice/subscribers')) ?>" class="<?= ($activeNav ?? '') === 'subscribers' ? 'active' : '' ?>" aria-label="Subscribers" title="Subscribers"><i class="fa-solid fa-envelope"></i><span class="dash-sidebar-label">Subscribers</span></a>
        <a href="<?= e(url('/backoffice/reports')) ?>" class="<?= ($activeNav ?? '') === 'reports' ? 'active' : '' ?>" aria-label="Reports" title="Reports"><i class="fa-solid fa-chart-line"></i><span class="dash-sidebar-label">Reports</span></a>
        <a href="<?= e(url('/backoffice/scheduled-tasks')) ?>" class="<?= ($activeNav ?? '') === 'scheduled-tasks' ? 'active' : '' ?>" aria-label="Scheduled tasks" title="Scheduled tasks"><i class="fa-solid fa-clock-rotate-left"></i><span class="dash-sidebar-label">Scheduled</span></a>
        <a href="<?= e(url('/backoffice/seo')) ?>" class="<?= ($activeNav ?? '') === 'seo' ? 'active' : '' ?>" aria-label="SEO" title="SEO"><i class="fa-solid fa-magnifying-glass-chart"></i><span class="dash-sidebar-label">SEO</span></a>
        <a href="<?= e(url('/backoffice/about')) ?>" class="<?= ($activeNav ?? '') === 'about' ? 'active' : '' ?>" aria-label="About page" title="About page"><i class="fa-solid fa-circle-info"></i><span class="dash-sidebar-label">About</span></a>
        <a href="<?= e(url('/backoffice/gateways')) ?>" class="<?= ($activeNav ?? '') === 'gateways' ? 'active' : '' ?>" aria-label="Payment gateways" title="Payment gateways"><i class="fa-solid fa-credit-card"></i><span class="dash-sidebar-label">Gateways</span></a>
        <a href="<?= e(url('/backoffice/settings')) ?>" class="<?= ($activeNav ?? '') === 'settings' ? 'active' : '' ?>" aria-label="Settings" title="Settings"><i class="fa-solid fa-gear"></i><span class="dash-sidebar-label">Settings</span></a>
      <?php else: ?>
        <?php $isProFounder = auth()->check() && auth()->user()->isPro(); ?>
        <a href="<?= e(url('/founder')) ?>" class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>" aria-label="Home" title="Home"><i class="fa-solid fa-house"></i><span class="dash-sidebar-label">Home</span></a>
        <a href="<?= e(url('/founder/startups')) ?>" class="<?= ($activeNav ?? '') === 'startups' ? 'active' : '' ?>" aria-label="My startup" title="My startups"><i class="fa-solid fa-building-user"></i><span class="dash-sidebar-label">Startups</span></a>
        <?php if ($isProFounder): ?>
        <a href="<?= e(url('/founder/badges')) ?>" class="<?= ($activeNav ?? '') === 'badges' ? 'active' : '' ?>" aria-label="Badges" title="Embed badges"><i class="fa-solid fa-certificate"></i><span class="dash-sidebar-label">Badges</span></a>
        <?php else: ?>
        <button type="button" class="dash-sidebar-link dash-sidebar-link--pro-gated<?= ($activeNav ?? '') === 'badges' ? ' active' : '' ?>" data-pro-toast="badges" aria-label="Badges (Pro)" title="Badges — Pro feature"><i class="fa-solid fa-certificate"></i><span class="dash-sidebar-label">Badges</span><span class="dash-sidebar-pro-lock" aria-hidden="true"><i class="fa-solid fa-crown"></i></span></button>
        <?php endif; ?>
        <a href="<?= e(url('/founder/upvotes')) ?>" class="<?= ($activeNav ?? '') === 'upvotes' ? 'active' : '' ?>" aria-label="Upvotes" title="Upvotes"><i class="fa-solid fa-arrow-up"></i><span class="dash-sidebar-label">Upvotes</span></a>
        <?php if ($isProFounder): ?>
        <a href="<?= e(url('/founder/fundraising')) ?>" class="<?= ($activeNav ?? '') === 'fundraising' ? 'active' : '' ?>" aria-label="Fund raising" title="Fund raising"><i class="fa-solid fa-hand-holding-dollar"></i><span class="dash-sidebar-label">Fund raising</span></a>
        <?php else: ?>
        <button type="button" class="dash-sidebar-link dash-sidebar-link--pro-gated<?= ($activeNav ?? '') === 'fundraising' ? ' active' : '' ?>" data-pro-toast="fundraising" aria-label="Fund raising (Pro)" title="Fund raising — Pro feature"><i class="fa-solid fa-hand-holding-dollar"></i><span class="dash-sidebar-label">Fund raising</span><span class="dash-sidebar-pro-lock" aria-hidden="true"><i class="fa-solid fa-crown"></i></span></button>
        <?php endif; ?>
        <?php if ($isProFounder): ?>
        <a href="<?= e(url('/founder/analytics')) ?>" class="<?= ($activeNav ?? '') === 'analytics' ? 'active' : '' ?>" aria-label="Analytics" title="Analytics"><i class="fa-solid fa-chart-line"></i><span class="dash-sidebar-label">Analytics</span></a>
        <a href="<?= e(url('/founder/blog')) ?>" class="<?= ($activeNav ?? '') === 'blog' ? 'active' : '' ?>" aria-label="Blog" title="Blog"><i class="fa-solid fa-pen-nib"></i><span class="dash-sidebar-label">Blog</span></a>
        <?php else: ?>
        <button type="button" class="dash-sidebar-link dash-sidebar-link--pro-gated<?= ($activeNav ?? '') === 'analytics' ? ' active' : '' ?>" data-pro-toast="analytics" aria-label="Analytics (Pro)" title="Analytics — Pro feature"><i class="fa-solid fa-chart-line"></i><span class="dash-sidebar-label">Analytics</span><span class="dash-sidebar-pro-lock" aria-hidden="true"><i class="fa-solid fa-crown"></i></span></button>
        <button type="button" class="dash-sidebar-link dash-sidebar-link--pro-gated<?= ($activeNav ?? '') === 'blog' ? ' active' : '' ?>" data-pro-toast="blog" aria-label="Blog (Pro)" title="Founder blog — Pro feature"><i class="fa-solid fa-pen-nib"></i><span class="dash-sidebar-label">Blog</span><span class="dash-sidebar-pro-lock" aria-hidden="true"><i class="fa-solid fa-crown"></i></span></button>
        <?php endif; ?>
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
        <footer class="dash-sister-footer" style="padding: 12px 20px 16px; border-top: 1px solid var(--d-border, #2a2e3d); margin-top: auto;">
          <?php $style = 'compact'; include __DIR__ . '/partials/sister-sites.php'; ?>
        </footer>
      </main>
    </div>
  </div>
  <div id="edenToastContainer" class="eden-toast-container" aria-live="polite"></div>
  <?php
    $edenShowProPopups = (($sidebar ?? '') === 'founder' && auth()->check() && ! auth()->user()->isPro());
    $edenSiteName = (function_exists('gs') && gs('site_name')) ? (string) gs('site_name') : 'Eden';
  ?>
  <?php if ($edenShowProPopups): ?>
  <div id="edenProPopupStack" class="eden-pro-popup-stack" aria-label="Pro membership offers"></div>
  <?php endif; ?>
  <style>
    .eden-toast-container{
      position:fixed;
      top:20px;
      right:20px;
      z-index:9998;
      display:flex;
      flex-direction:column;
      gap:10px;
      max-width:min(440px,calc(100vw - 32px));
      pointer-events:none
    }
    .eden-toast{
      pointer-events:auto;
      padding:14px 18px;
      border-radius:10px;
      font-size:0.95rem;
      line-height:1.5;
      box-shadow:0 10px 30px rgba(0,0,0,0.35);
      border:1px solid var(--d-border,#2a2e3d);
      background:var(--d-surface,#12141c);
      color:var(--d-text,#e8eaef);
      transform-origin:top right;
      animation:eden-toast-in 0.35s cubic-bezier(0.18,0.89,0.32,1.28)
    }
    .eden-toast--promo{
      border-left:4px solid var(--accent,#00d4aa)
    }
    .eden-toast--promo .eden-toast-cta{
      display:inline-block;
      margin-top:8px;
      font-weight:600;
      color:var(--accent);
      text-decoration:none;
      font-size:0.875rem
    }
    .eden-toast--promo .eden-toast-cta:hover{
      text-decoration:underline
    }
    @keyframes eden-toast-in{
      from{
        opacity:0;
        transform:translateY(-10px) translateX(10px) scale(0.96)
      }
      to{
        opacity:1;
        transform:translateY(0) translateX(0) scale(1)
      }
    }
    .eden-pro-popup-stack{
      position:fixed;
      top:88px;
      right:20px;
      z-index:9997;
      display:flex;
      flex-direction:column;
      align-items:flex-end;
      gap:14px;
      max-width:min(440px,calc(100vw - 28px));
      max-height:calc(100vh - 100px);
      overflow-y:auto;
      overflow-x:hidden;
      -webkit-overflow-scrolling:touch;
      pointer-events:auto
    }
    .eden-pro-popup{
      pointer-events:auto;
      position:relative;
      width:100%;
      min-width:min(400px,calc(100vw - 28px));
      max-width:440px;
      padding:24px 26px 22px;
      border-radius:16px;
      background:linear-gradient(145deg,rgba(18,20,28,0.98) 0%,rgba(22,26,38,0.98) 100%);
      border:1px solid rgba(0,212,170,0.35);
      box-shadow:0 18px 48px rgba(0,0,0,0.45),0 0 0 1px rgba(255,255,255,0.04) inset,0 0 40px rgba(0,212,170,0.08);
      color:var(--d-text,#e8eaef);
      transform-origin:top right;
      opacity:0;
      transform:translate3d(32px,-24px,0) scale(0.94);
      animation:eden-pro-popup-in 0.65s cubic-bezier(0.22,1,0.36,1) forwards;
      will-change:transform,opacity
    }
    @media (max-width:640px){
      .eden-pro-popup-stack{top:72px;right:14px;max-width:calc(100vw - 20px)}
      .eden-pro-popup{min-width:0;padding:20px 20px 18px;border-radius:14px}
    }
    @media (prefers-reduced-motion:reduce){
      .eden-pro-popup{animation:none;opacity:1;transform:none}
    }
    @keyframes eden-pro-popup-in{
      to{
        opacity:1;
        transform:translate3d(0,0,0) scale(1)
      }
    }
    .eden-pro-popup.is-leaving{
      animation:eden-pro-popup-out 0.38s ease forwards
    }
    @keyframes eden-pro-popup-out{
      to{
        opacity:0;
        transform:translate3d(28px,-12px,0) scale(0.96)
      }
    }
    .eden-pro-popup__close{
      position:absolute;
      top:12px;
      right:12px;
      width:36px;
      height:36px;
      border:none;
      border-radius:10px;
      background:rgba(255,255,255,0.06);
      color:var(--d-text-secondary,#94a3b8);
      cursor:pointer;
      display:flex;
      align-items:center;
      justify-content:center;
      transition:background 0.2s,color 0.2s
    }
    .eden-pro-popup__close:hover{
      background:rgba(255,255,255,0.1);
      color:var(--d-text,#e8eaef)
    }
    .eden-pro-popup__badge{
      display:inline-flex;
      align-items:center;
      gap:6px;
      font-size:0.7rem;
      font-weight:700;
      letter-spacing:0.06em;
      text-transform:uppercase;
      color:#00d4aa;
      margin-bottom:10px
    }
    .eden-pro-popup__title{
      font-size:1.2rem;
      font-weight:700;
      line-height:1.25;
      margin:0 0 10px;
      padding-right:36px
    }
    .eden-pro-popup__body{
      font-size:0.94rem;
      line-height:1.55;
      color:var(--d-text-secondary,#94a3b8);
      margin:0 0 18px
    }
    .eden-pro-popup__cta{
      display:inline-flex;
      align-items:center;
      gap:8px;
      padding:12px 20px;
      border-radius:10px;
      font-weight:600;
      font-size:0.9rem;
      text-decoration:none;
      color:#0f172a;
      background:linear-gradient(135deg,#00d4aa,#00b894);
      box-shadow:0 4px 16px rgba(0,212,170,0.35);
      transition:transform 0.2s,box-shadow 0.2s
    }
    .eden-pro-popup__cta:hover{
      transform:translateY(-1px);
      box-shadow:0 8px 22px rgba(0,212,170,0.45);
      color:#0f172a
    }
    .eden-pro-popup__cta i{font-size:1rem}
  </style>
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
    (function() {
      var container = document.getElementById('edenToastContainer');
      if (!container) return;
      var pricingUrl = '<?= e(url('/pricing')) ?>';
      function showToast(type, msg, options) {
        options = options || {};
        var el = document.createElement('div');
        el.className = 'eden-toast eden-toast--' + (type || 'info');
        el.appendChild(document.createTextNode(msg));
        var cta = options.ctaText && options.ctaHref;
        if (cta) {
          el.appendChild(document.createTextNode(' '));
          var a = document.createElement('a');
          a.href = options.ctaHref;
          a.className = 'eden-toast-cta';
          a.textContent = options.ctaText;
          el.appendChild(a);
        }
        container.appendChild(el);
        var duration = options.duration !== undefined ? options.duration : (cta ? 6000 : 4000);
        setTimeout(function() {
          el.style.opacity = '0';
          el.style.transform = 'translateY(-4px)';
          setTimeout(function() { if (el.parentNode) el.parentNode.removeChild(el); }, 200);
        }, duration);
      }
      window.edenPromoToast = function(opts) {
        var key = opts.key;
        if (key) {
          try {
            if (sessionStorage.getItem('eden_promo_' + key)) return;
            sessionStorage.setItem('eden_promo_' + key, '1');
          } catch (e) {}
        }
        showToast('promo', opts.message || '', { ctaText: opts.ctaText, ctaHref: opts.ctaHref || '#', duration: opts.duration });
      };
      window.edenPricingUrl = pricingUrl;

      var proToastCopy = {
        badges: 'Embed trust badges on your site — Pro members get full badge access.',
        analytics: 'See views, clicks, and trends for your listings. Upgrade to Pro for analytics.',
        blog: 'Write posts as a founder on the blog. Pro unlocks the founder blog.',
        fundraising: 'Show that you are raising and manage investor-facing funding details. Upgrade to Pro for fund raising.'
      };
      document.querySelectorAll('.dash-sidebar-link--pro-gated').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          var key = btn.getAttribute('data-pro-toast') || 'badges';
          var msg = proToastCopy[key] || 'This feature is part of Pro.';
          showToast('promo', msg, { ctaText: 'View Pro pricing', ctaHref: pricingUrl, duration: 7000 });
          var sb = document.getElementById('dashSidebar');
          var bd = document.getElementById('dashSidebarBackdrop');
          if (sb) sb.classList.remove('is-open');
          if (bd) bd.classList.remove('is-open');
        });
      });
    })();
  </script>
  <?php if (! empty($edenShowProPopups)): ?>
  <script>
    (function() {
      var stack = document.getElementById('edenProPopupStack');
      if (!stack) return;
      var pricingUrl = <?= json_encode(url('/pricing')) ?>;
      var siteName = <?= json_encode($edenSiteName) ?>;

      var popups = [
        {
          id: 'analytics',
          title: 'See who engages with your listing',
          body: 'Pro unlocks analytics — views, clicks, and trends so you can sharpen your pitch and spot real interest.',
          cta: 'Explore Pro features',
          icon: 'fa-chart-line'
        },
        {
          id: 'badges',
          title: 'Trust badges for your website',
          body: 'Show visitors you are listed on ' + siteName + ' with embeddable badges. Pro members get full badge access.',
          cta: 'Upgrade for badges',
          icon: 'fa-certificate'
        },
        {
          id: 'scale',
          title: 'List every product you ship',
          body: 'Free tier is limited. Pro gives unlimited startups, blog posts, hero placement requests, and priority support — $9.99 once, lifetime.',
          cta: 'Go Pro — $9.99 lifetime',
          icon: 'fa-layer-group'
        },
        {
          id: 'hero',
          title: 'Get featured on the homepage',
          body: 'Request a hero spotlight for your startup. Pro founders can submit — stand out where new visitors look first.',
          cta: 'Unlock with Pro',
          icon: 'fa-star'
        }
      ];

      var STORAGE_PREFIX = 'eden_pro_popup_dismissed_v1_';

      function dismissed(id) {
        try {
          return sessionStorage.getItem(STORAGE_PREFIX + id) === '1';
        } catch (e) {
          return false;
        }
      }

      function rememberDismiss(id) {
        try {
          sessionStorage.setItem(STORAGE_PREFIX + id, '1');
        } catch (e) {}
      }

      function removeEl(el) {
        if (el && el.parentNode) el.parentNode.removeChild(el);
      }

      function buildPopup(item) {
        if (dismissed(item.id)) return;
        var wrap = document.createElement('article');
        wrap.className = 'eden-pro-popup';
        wrap.setAttribute('role', 'dialog');
        wrap.setAttribute('aria-labelledby', 'eden-pro-title-' + item.id);

        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'eden-pro-popup__close';
        btn.setAttribute('aria-label', 'Dismiss');
        btn.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
        btn.addEventListener('click', function() {
          rememberDismiss(item.id);
          wrap.classList.add('is-leaving');
          setTimeout(function() { removeEl(wrap); }, 380);
        });

        var badge = document.createElement('div');
        badge.className = 'eden-pro-popup__badge';
        badge.innerHTML = '<i class="fa-solid ' + item.icon + '" aria-hidden="true"></i> Pro';

        var h = document.createElement('h2');
        h.className = 'eden-pro-popup__title';
        h.id = 'eden-pro-title-' + item.id;
        h.textContent = item.title;

        var p = document.createElement('p');
        p.className = 'eden-pro-popup__body';
        p.textContent = item.body;

        var a = document.createElement('a');
        a.href = pricingUrl;
        a.className = 'eden-pro-popup__cta';
        a.innerHTML = '<i class="fa-solid fa-crown" aria-hidden="true"></i> ' + item.cta;

        wrap.appendChild(btn);
        wrap.appendChild(badge);
        wrap.appendChild(h);
        wrap.appendChild(p);
        wrap.appendChild(a);
        stack.appendChild(wrap);
      }

      var step = 2200;
      var pending = popups.filter(function(item) {
        return !dismissed(item.id);
      });
      pending.forEach(function(item, i) {
        setTimeout(function() {
          buildPopup(item);
        }, i * step);
      });
    })();
  </script>
  <?php endif; ?>
  <?= $scriptDeps ?? '' ?>
  <?= $notifyPartial ?? '' ?>
  <?= $scripts ?? '' ?>
</body>
</html>

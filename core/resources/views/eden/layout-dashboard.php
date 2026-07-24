<!DOCTYPE html>
<html lang="en" class="dashboard">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if (function_exists('csrf_token')): ?><meta name="csrf-token" content="<?= e(csrf_token()) ?>"><?php endif; ?>
  <title><?= e($title ?? 'Dashboard') ?> | <?= e($dashboardLogo ?? 'Eden') ?></title>
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
  <a class="dash-skip-link" href="#dashboardMain">Skip to dashboard content</a>
  <div class="dash-layout" id="dashLayout">
    <div class="dash-sidebar-backdrop" id="dashSidebarBackdrop" aria-hidden="true"></div>
    <aside class="dash-sidebar" id="dashSidebar" aria-label="<?= ($sidebar ?? '') === 'admin' ? 'Admin' : 'Founder' ?> navigation">
      <button type="button" class="dash-sidebar-expand-toggle" id="dashSidebarExpandToggle" aria-label="Expand sidebar" title="Expand sidebar" aria-controls="dashSidebar" aria-expanded="false">
        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
      </button>
      <?php
        $currentNav = $activeNav ?? 'home';
        $navLink = function (string $url, string $key, string $label, string $icon, string $badge = '') use ($currentNav): string {
            $active = $currentNav === $key;
            return '<a href="' . e($url) . '" class="' . ($active ? 'active' : '') . '" aria-label="' . e($label) . '" title="' . e($label) . '"' .
                ($active ? ' aria-current="page"' : '') . '><i class="fa-solid ' . e($icon) . '" aria-hidden="true"></i><span class="dash-sidebar-label">' .
                e($label) . '</span>' . $badge . '</a>';
        };
        $navBadge = function (int $count, string $label): string {
            if ($count < 1) {
                return '';
            }
            return '<span class="dash-nav-badge" aria-label="' . $count . ' ' . e($label) . '">' . $count . '</span>';
        };
      ?>
      <?php if (($sidebar ?? '') === 'admin'): ?>
        <?php
          $admin = auth()->guard('admin')->user();
          $adminGroups = [
            'Content' => [
              ['startups', 'startups', 'Startups', 'fa-rocket', $navBadge((int) ($adminPendingStartupsBadge ?? 0), 'pending'), 'admin.startups.index'],
              ['website_health', 'startup-websites', 'Website health', 'fa-globe', '', 'admin.startup-websites.index'],
              ['categories', 'categories', 'Categories', 'fa-folder-tree', '', 'admin.categories.index'],
              ['blog', 'blog', 'Blog', 'fa-pen-nib', '', 'admin.blog.index'],
            ],
            'People' => [
              ['users', 'users', 'Users', 'fa-user', '', 'admin.users.index'],
              ['messages', 'contact-messages', 'Messages', 'fa-message', $navBadge((int) ($adminUnseenMessagesBadge ?? 0), 'unseen'), 'admin.contact-messages.index'],
              ['subscribers', 'subscribers', 'Subscribers', 'fa-envelope', '', 'admin.subscribers.index'],
            ],
            'Operations' => [
              ['moderation', 'moderation', 'Moderation', 'fa-shield-halved', '', 'admin.operations.moderation'],
              ['reports', 'startup-reports', 'Listing flags', 'fa-flag', $navBadge((int) ($adminPendingListingReportsBadge ?? 0), 'pending'), 'admin.startup-reports.index'],
              ['reports', 'reports', 'Reports', 'fa-chart-line', '', 'admin.reports.index'],
              ['scheduled_tasks', 'scheduled-tasks', 'Scheduled tasks', 'fa-clock-rotate-left', '', 'admin.scheduled-tasks.index'],
              ['admin_notifications', 'admin-notifications', 'Notifications', 'fa-bell', '', 'admin.operations.notifications'],
            ],
            'Revenue' => [
              ['payments', 'payments', 'Payment ledger', 'fa-receipt', '', 'admin.operations.payments'],
              ['advertising', 'ad-spots', 'Ad Spots', 'fa-rectangle-ad', '', 'admin.ad-spots.index'],
              ['gateways', 'gateways', 'Gateways', 'fa-credit-card', '', 'admin.gateways.index'],
            ],
            'Configuration' => [
              ['settings', 'seo', 'SEO', 'fa-magnifying-glass-chart', '', 'admin.seo'],
              ['settings', 'about', 'About page', 'fa-circle-info', '', 'admin.about'],
              ['settings', 'settings', 'Settings', 'fa-gear', '', 'admin.settings.index'],
            ],
          ];
        ?>
        <div class="dash-nav-group">
          <span class="dash-nav-group-label">Overview</span>
          <?= $navLink(route('admin.dashboard'), 'home', 'Home', 'fa-house') ?>
        </div>
        <?php foreach ($adminGroups as $groupLabel => $items): ?>
          <?php $visibleItems = array_values(array_filter($items, fn ($item) => $admin && $admin->hasModule($item[0]))); ?>
          <?php if ($visibleItems): ?>
          <div class="dash-nav-group">
            <span class="dash-nav-group-label"><?= e($groupLabel) ?></span>
            <?php foreach ($visibleItems as $item): ?>
              <?= $navLink(route($item[5]), $item[1], $item[2], $item[3], $item[4]) ?>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        <?php endforeach; ?>
        <?php if ($admin?->isSuperAdmin()): ?>
        <div class="dash-nav-group">
          <span class="dash-nav-group-label">Advanced</span>
          <?= $navLink(route('admin.migration.index'), 'migrations', 'Migrations', 'fa-database') ?>
          <?= $navLink(route('admin.staff.index'), 'staff', 'Staff & RBAC', 'fa-user-shield') ?>
          <?= $navLink(route('admin.operations.audit'), 'audit', 'Audit log', 'fa-clipboard-list') ?>
          <?= $navLink(route('admin.operations.health'), 'health', 'System health', 'fa-heart-pulse') ?>
        </div>
        <?php endif; ?>
      <?php else: ?>
        <?php $isProFounder = auth()->check() && auth()->user()->isPro(); ?>
        <?php
          $founderNavStatus = $founderNavStatus ?? [];
          $startupAttentionCount = (int) ($founderNavStatus['claims'] ?? 0) + (int) ($founderNavStatus['cofounders'] ?? 0);
        ?>
        <div class="dash-nav-group">
          <span class="dash-nav-group-label">Workspace</span>
          <?= $navLink(route('founder.dashboard'), 'home', 'Home', 'fa-house') ?>
          <?= $navLink(route('founder.startups.index'), 'startups', 'Startups', 'fa-building-user', $navBadge($startupAttentionCount, 'startup updates')) ?>
          <?= $navLink(route('saved'), 'saved', 'Saved startups', 'fa-bookmark', $navBadge((int) ($founderNavStatus['saved'] ?? 0), 'saved')) ?>
          <?= $navLink(route('founder.comments.index'), 'comments', 'Comments', 'fa-comments') ?>
          <?= $navLink(route('founder.badges'), 'badges', 'Badges', 'fa-certificate', $navBadge((int) ($founderNavStatus['awards'] ?? 0), 'awards')) ?>
        </div>
        <div class="dash-nav-group">
          <span class="dash-nav-group-label">Grow</span>
        <?php if ($isProFounder): ?>
          <?= $navLink(route('founder.fundraising.index'), 'fundraising', 'Fund raising', 'fa-hand-holding-dollar', $navBadge((int) ($founderNavStatus['investors'] ?? 0), 'new investor leads')) ?>
        <?php else: ?>
        <button type="button" class="dash-sidebar-link dash-sidebar-link--pro-gated<?= ($activeNav ?? '') === 'fundraising' ? ' active' : '' ?>" data-pro-toast="fundraising" aria-label="Fund raising (Pro)" title="Fund raising — Pro feature"><i class="fa-solid fa-hand-holding-dollar"></i><span class="dash-sidebar-label">Fund raising</span><span class="dash-sidebar-pro-lock" aria-hidden="true"><i class="fa-solid fa-crown"></i></span></button>
        <?php endif; ?>
        <?php if ($isProFounder): ?>
          <?= $navLink(route('founder.analytics'), 'analytics', 'Analytics', 'fa-chart-line') ?>
          <?= $navLink(route('founder.blog.index'), 'blog', 'Blog', 'fa-pen-nib') ?>
        <?php else: ?>
        <button type="button" class="dash-sidebar-link dash-sidebar-link--pro-gated<?= ($activeNav ?? '') === 'analytics' ? ' active' : '' ?>" data-pro-toast="analytics" aria-label="Analytics (Pro)" title="Analytics — Pro feature"><i class="fa-solid fa-chart-line"></i><span class="dash-sidebar-label">Analytics</span><span class="dash-sidebar-pro-lock" aria-hidden="true"><i class="fa-solid fa-crown"></i></span></button>
        <button type="button" class="dash-sidebar-link dash-sidebar-link--pro-gated<?= ($activeNav ?? '') === 'blog' ? ' active' : '' ?>" data-pro-toast="blog" aria-label="Blog (Pro)" title="Founder blog — Pro feature"><i class="fa-solid fa-pen-nib"></i><span class="dash-sidebar-label">Blog</span><span class="dash-sidebar-pro-lock" aria-hidden="true"><i class="fa-solid fa-crown"></i></span></button>
        <?php endif; ?>
          <?= $navLink(route('founder.revenue-api'), 'revenue-api', 'Revenue API', 'fa-code') ?>
        </div>
        <div class="dash-nav-group">
          <span class="dash-nav-group-label">Account</span>
          <?php $founderUnreadCount = auth()->user()->unreadNotifications()->count(); ?>
          <a href="<?= e(route('founder.notifications.index')) ?>" class="dash-sidebar-link<?= ($activeNav ?? '') === 'notifications' ? ' active' : '' ?>" aria-label="Notifications<?= $founderUnreadCount ? ' (' . $founderUnreadCount . ' unread)' : '' ?>">
            <i class="fa-solid fa-bell"></i><span class="dash-sidebar-label">Notifications</span>
            <?php if ($founderUnreadCount): ?><span class="dash-badge dash-badge-danger"><?= $founderUnreadCount > 99 ? '99+' : $founderUnreadCount ?></span><?php endif; ?>
          </a>
          <?= $navLink(route('founder.settings'), 'settings', 'Settings', 'fa-gear') ?>
        </div>
      <?php endif; ?>
      <div class="dash-nav-bottom">
        <a href="<?= e(url('/')) ?>" aria-label="Back to Eden" title="Back to site"><i class="fa-solid fa-arrow-left"></i><span class="dash-sidebar-label">Back to site</span></a>
      </div>
    </aside>
    <div class="dash-body">
      <header class="dash-topbar">
        <button type="button" class="dash-sidebar-toggle" id="dashSidebarToggle" aria-label="Open menu" aria-controls="dashSidebar" aria-expanded="false"><i class="fa-solid fa-bars" aria-hidden="true"></i></button>
        <div class="dash-topbar-left">
          <a href="<?= e($sidebar === 'admin' ? url('/backoffice') : url('/founder')) ?>" class="dash-logo">
            <span class="dash-logo-dots"><span></span><span></span><span></span><span></span></span>
            <?= e($dashboardLogo ?? 'Eden') ?>
          </a>
          <?= $dashboardTopbar ?? '' ?>
        </div>
        <div class="dash-search-wrap">
          <i class="fa-solid fa-magnifying-glass dash-search-icon" aria-hidden="true"></i>
          <input type="search" class="dash-search" id="dashGlobalSearch" placeholder="<?= e($searchPlaceholder ?? 'Search') ?>" aria-label="<?= ($sidebar ?? '') === 'admin' ? 'Search permitted admin modules' : 'Search your startups' ?>" aria-controls="dashSearchResults" aria-expanded="false" autocomplete="off" data-search-url="<?= e(($sidebar ?? '') === 'admin' ? route('admin.search') : route('founder.search')) ?>">
          <div class="dash-search-results" id="dashSearchResults" role="listbox" aria-label="Search results" hidden></div>
        </div>
        <div class="dash-topbar-right">
          <a href="<?= e(url('/contact')) ?>" aria-label="Help" title="Contact / Help"><i class="fa-solid fa-circle-question" aria-hidden="true"></i></a>
          <?php if (($sidebar ?? '') === 'admin' && function_exists('route') && auth()->guard('admin')->check()): ?>
            <form action="<?= e(route('admin.logout')) ?>" method="POST" class="dash-inline-form">
              <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
              <button type="submit" class="dash-logout" aria-label="Log out" title="Log out"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i></button>
            </form>
          <?php endif; ?>
          <?php if (($sidebar ?? '') === 'founder' && auth()->check()): ?>
            <form action="<?= e(route('logout')) ?>" method="POST" class="dash-inline-form">
              <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
              <button type="submit" class="dash-logout" aria-label="Log out" title="Log out"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i></button>
            </form>
          <?php endif; ?>
          <?php
            $avatarUrl = null;
            if (($sidebar ?? '') === 'founder' && auth()->check()) {
                $avatarUrl = route('founder.settings');
            } elseif (($sidebar ?? '') === 'admin') {
                $avatarUrl = route('admin.security.profile');
            }
          ?>
          <?php if ($avatarUrl): ?>
            <a href="<?= e($avatarUrl) ?>" class="dash-avatar" aria-label="Open profile settings" title="<?= e($avatarTitle ?? 'Account') ?>"><?= e($avatarLetter ?? '?') ?></a>
          <?php else: ?>
            <div class="dash-avatar" title="<?= e($avatarTitle ?? 'Account') ?>" aria-label="<?= e($avatarTitle ?? 'Account') ?>"><?= e($avatarLetter ?? '?') ?></div>
          <?php endif; ?>
        </div>
      </header>
      <main class="dash-main" id="dashboardMain" tabindex="-1">
        <div class="dash-content">
          <?= $content ?? '' ?>
        </div>
        <footer class="dash-sister-footer">
          <?php $style = 'compact'; include __DIR__ . '/partials/sister-sites.php'; ?>
        </footer>
      </main>
    </div>
  </div>
  <div class="dash-dialog" id="dashboardConfirmDialog" role="dialog" aria-modal="true" aria-labelledby="dashboardConfirmTitle" aria-describedby="dashboardConfirmMessage" hidden>
    <div class="dash-dialog-backdrop" data-confirm-cancel></div>
    <div class="dash-dialog-panel">
      <h2 id="dashboardConfirmTitle">Confirm action</h2>
      <p id="dashboardConfirmMessage"></p>
      <div class="dash-dialog-actions">
        <button type="button" class="dash-btn dash-btn-secondary" data-confirm-cancel>Cancel</button>
        <button type="button" class="dash-btn dash-btn-danger" id="dashboardConfirmSubmit" data-dialog-initial-focus>Confirm</button>
      </div>
    </div>
  </div>
  <div id="edenToastContainer" class="eden-toast-container" aria-live="polite"></div>
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
          var isOpen = sidebar.classList.toggle('is-open');
          toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
          toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
          if (backdrop) {
            backdrop.classList.toggle('is-open', isOpen);
          }
        });
        if (backdrop) {
          backdrop.addEventListener('click', function() {
            sidebar.classList.remove('is-open');
            backdrop.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Open menu');
            toggle.focus();
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
            expandBtn.setAttribute('aria-expanded', 'true');
            var icon = expandBtn.querySelector('i');
            if (icon) { icon.className = 'fa-solid fa-chevron-left'; }
          }
          try { localStorage.setItem(STORAGE_KEY, '1'); } catch (e) {}
        } else {
          layout.classList.remove('dash-sidebar-expanded');
          if (expandBtn) {
            expandBtn.setAttribute('aria-label', 'Expand sidebar');
            expandBtn.setAttribute('title', 'Expand sidebar');
            expandBtn.setAttribute('aria-expanded', 'false');
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

      document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && sidebar && sidebar.classList.contains('is-open')) {
          sidebar.classList.remove('is-open');
          if (backdrop) backdrop.classList.remove('is-open');
          if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
            toggle.setAttribute('aria-label', 'Open menu');
            toggle.focus();
          }
        }
      });
    })();
    (function() {
      var input = document.getElementById('dashGlobalSearch');
      var results = document.getElementById('dashSearchResults');
      if (!input || !results) return;
      var timer = null;
      var controller = null;

      function closeResults() {
        results.hidden = true;
        results.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
      }

      function renderResults(groups) {
        results.innerHTML = '';
        var itemCount = 0;
        groups.forEach(function(group) {
          if (!group.items || !group.items.length) return;
          var section = document.createElement('div');
          section.className = 'dash-search-group';
          var label = document.createElement('div');
          label.className = 'dash-search-group-label';
          label.textContent = group.label;
          section.appendChild(label);
          group.items.forEach(function(item) {
            var link = document.createElement('a');
            link.href = item.url;
            link.className = 'dash-search-result';
            link.setAttribute('role', 'option');
            var title = document.createElement('strong');
            title.textContent = item.label;
            var description = document.createElement('span');
            description.textContent = item.description || '';
            link.appendChild(title);
            link.appendChild(description);
            section.appendChild(link);
            itemCount += 1;
          });
          results.appendChild(section);
        });
        if (itemCount === 0) {
          var empty = document.createElement('p');
          empty.className = 'dash-search-empty';
          empty.textContent = 'No matching results';
          results.appendChild(empty);
        }
        results.hidden = false;
        input.setAttribute('aria-expanded', 'true');
      }

      function runSearch() {
        var query = input.value.trim();
        if (query.length < 2) {
          closeResults();
          return;
        }
        if (controller) controller.abort();
        controller = new AbortController();
        var separator = input.dataset.searchUrl.indexOf('?') === -1 ? '?' : '&';
        fetch(input.dataset.searchUrl + separator + 'q=' + encodeURIComponent(query), {
          headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
          signal: controller.signal
        }).then(function(response) {
          if (!response.ok) throw new Error('Search failed');
          return response.json();
        }).then(function(data) {
          renderResults(data.groups || []);
        }).catch(function(error) {
          if (error.name !== 'AbortError') closeResults();
        });
      }

      input.addEventListener('input', function() {
        clearTimeout(timer);
        timer = setTimeout(runSearch, 220);
      });
      input.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
          closeResults();
        } else if (event.key === 'ArrowDown' && !results.hidden) {
          var firstResult = results.querySelector('.dash-search-result');
          if (firstResult) {
            event.preventDefault();
            firstResult.focus();
          }
        }
      });
      results.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
          closeResults();
          input.focus();
        }
      });
      document.addEventListener('click', function(event) {
        if (!event.target.closest('.dash-search-wrap')) closeResults();
      });
    })();
    (function() {
      var activeDialog = null;
      var returnFocus = null;
      var focusableSelector = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

      function openDialog(dialog, trigger) {
        if (!dialog) return;
        activeDialog = dialog;
        returnFocus = trigger || document.activeElement;
        dialog.removeAttribute('hidden');
        dialog.style.display = 'flex';
        document.body.classList.add('dash-dialog-open');
        var focusTarget = dialog.querySelector('[data-dialog-initial-focus]') || dialog.querySelector(focusableSelector);
        if (focusTarget) focusTarget.focus();
      }

      function closeDialog(dialog) {
        var target = dialog || activeDialog;
        if (!target) return;
        target.setAttribute('hidden', '');
        target.style.display = 'none';
        document.body.classList.remove('dash-dialog-open');
        activeDialog = null;
        if (returnFocus && typeof returnFocus.focus === 'function') returnFocus.focus();
        returnFocus = null;
      }

      document.addEventListener('keydown', function(event) {
        if (!activeDialog) return;
        if (event.key === 'Escape') {
          event.preventDefault();
          closeDialog(activeDialog);
          return;
        }
        if (event.key !== 'Tab') return;
        var focusable = Array.prototype.slice.call(activeDialog.querySelectorAll(focusableSelector));
        if (!focusable.length) {
          event.preventDefault();
          return;
        }
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
          event.preventDefault();
          last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
          event.preventDefault();
          first.focus();
        }
      });

      window.EdenDashboardDialog = {
        open: openDialog,
        close: closeDialog
      };
    })();
    (function() {
      var dialog = document.getElementById('dashboardConfirmDialog');
      var message = document.getElementById('dashboardConfirmMessage');
      var submit = document.getElementById('dashboardConfirmSubmit');
      var pendingForm = null;
      if (!dialog || !message || !submit) return;

      document.addEventListener('submit', function(event) {
        var form = event.target.closest('form[data-confirm]');
        if (!form || form.dataset.confirmed === 'true') return;
        event.preventDefault();
        pendingForm = form;
        message.textContent = form.dataset.confirm || 'Are you sure you want to continue?';
        submit.textContent = form.dataset.confirmLabel || 'Confirm';
        window.EdenDashboardDialog.open(dialog, form.querySelector('button[type="submit"]'));
      });
      dialog.querySelectorAll('[data-confirm-cancel]').forEach(function(button) {
        button.addEventListener('click', function() {
          pendingForm = null;
          window.EdenDashboardDialog.close(dialog);
        });
      });
      submit.addEventListener('click', function() {
        if (!pendingForm) return;
        var form = pendingForm;
        pendingForm = null;
        form.dataset.confirmed = 'true';
        window.EdenDashboardDialog.close(dialog);
        form.requestSubmit();
      });
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
        analytics: 'See views, clicks, and trends for your listings. Upgrade to Pro for analytics.',
        blog: 'Write posts as a founder on the blog. Pro unlocks the founder blog.',
        fundraising: 'Show that you are raising and manage investor-facing funding details. Upgrade to Pro for fund raising.'
      };
      document.querySelectorAll('.dash-sidebar-link--pro-gated').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
          e.preventDefault();
          var key = btn.getAttribute('data-pro-toast') || 'analytics';
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
  <?= $scriptDeps ?? '' ?>
  <?= $notifyPartial ?? '' ?>
  <?= $scripts ?? '' ?>
</body>
</html>

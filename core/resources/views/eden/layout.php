<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
    $siteName = function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden';
    $pageTitleFinal = isset($pageTitle) ? $pageTitle : (isset($title) ? $title . ' — ' . $siteName : $siteName . ' — Startup Directory');
    $metaDesc = isset($metaDescription) ? $metaDescription : (function_exists('gs') && gs('meta_description') ? gs('meta_description') : (function_exists('gs') && gs('social_description') ? gs('social_description') : 'Startup directory for discoverability and growth.'));
    $socialDesc = isset($metaDescription) ? $metaDescription : (function_exists('gs') && gs('social_description') ? gs('social_description') : $metaDesc);
    $metaKeywordsFinal = isset($metaKeywords) ? $metaKeywords : (function_exists('gs') && gs('meta_keywords') ? gs('meta_keywords') : '');
    $seoImageUrl = isset($metaImage) ? $metaImage : (function_exists('gs') && gs('seo_image') ? url(asset(gs('seo_image'))) : '');
    $canonicalUrl = isset($canonicalUrl) ? $canonicalUrl : url()->current();
  ?>
  <title><?= e($pageTitleFinal) ?></title>
  <link rel="icon" type="image/png" href="<?= e(asset('images/favicon.png')) ?>">
  <script>
    (function(){var t=localStorage.getItem('eden_theme')||'light';document.documentElement.setAttribute('data-theme',t);})();
  </script>
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <?php if ($metaKeywordsFinal !== ''): ?><meta name="keywords" content="<?= e($metaKeywordsFinal) ?>"><?php endif; ?>
  <meta name="description" content="<?= e($metaDesc) ?>">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <meta property="og:title" content="<?= e($pageTitleFinal) ?>">
  <meta property="og:description" content="<?= e($socialDesc) ?>">
  <meta property="og:site_name" content="<?= e($siteName) ?>">
  <?php if ($seoImageUrl): ?><meta property="og:image" content="<?= e($seoImageUrl) ?>"><?php endif; ?>
  <meta name="twitter:card" content="<?= $seoImageUrl ? 'summary_large_image' : 'summary' ?>">
  <meta name="twitter:title" content="<?= e($pageTitleFinal) ?>">
  <meta name="twitter:description" content="<?= e($socialDesc) ?>">
  <?php if ($seoImageUrl): ?><meta name="twitter:image" content="<?= e($seoImageUrl) ?>"><?php endif; ?>
  <?php if (isset($structuredData) && is_array($structuredData) && !empty($structuredData)): ?>
  <script type="application/ld+json"><?= json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <?php $cssPath = public_path('css/main.css'); $cssVersion = file_exists($cssPath) ? substr(md5_file($cssPath), 0, 12) : ''; ?>
  <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?><?= $cssVersion ? '?v=' . $cssVersion : '' ?>">
  <?php
  $adsenseScript = (function_exists('gs') && gs('adsense_enabled')) ? trim((string)(gs('adsense_script') ?? '')) : '';
  echo $adsenseScript !== '' ? "\n  " . $adsenseScript . "\n" : '';
  ?>
</head>
<body>
  <div class="bg-grid"></div>
  <div class="bg-glow"></div>

  <header class="site-header">
    <div class="wrap header-inner">
      <a href="<?= e(url('/')) ?>" class="logo"><?= e(function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden') ?></a>
      <nav class="nav-main">
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle light or dark mode" aria-pressed="false" title="Toggle theme">
          <span class="theme-icon-dark" aria-hidden="true"><i class="fa-solid fa-moon"></i></span>
          <span class="theme-icon-light" aria-hidden="true"><i class="fa-solid fa-sun"></i></span>
        </button>
        <a href="<?= e(url('/launching-today')) ?>">Launching today</a>
        <a href="<?= e(url('/leaderboard')) ?>">Leaderboard</a>
        <a href="<?= e(url('/categories')) ?>">Categories</a>
        <a href="<?= e(url('/blog')) ?>">Blog</a>
        <a href="<?= e(url('/submit')) ?>">Submit</a>
        <a href="<?= e(url('/about')) ?>">About</a>
        <a href="<?= e(url('/contact')) ?>">Contact</a>
        <?php if (auth()->check()): ?>
        <a href="<?= e(url('/founder')) ?>" class="btn btn-ghost"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard</a>
        <form action="<?= e(route('logout')) ?>" method="POST" class="nav-logout-form">
          <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
          <button type="submit" class="btn btn-primary">Log out</button>
        </form>
        <?php else: ?>
        <a href="<?= e(url('/login')) ?>" class="btn btn-ghost" data-modal="login">Log in</a>
        <a href="<?= e(url('/register')) ?>" class="btn btn-primary" data-modal="signup">Sign up</a>
        <?php endif; ?>
      </nav>
      <button type="button" class="nav-toggle" aria-label="Open menu" id="navToggle"><i class="fa-solid fa-bars"></i></button>
    </div>
  </header>

  <main>
    <?php if (session('success')): ?>
    <div class="wrap" style="padding-top: 16px;">
      <div class="flash flash-success"><?= e(session('success')) ?></div>
    </div>
    <?php endif; ?>
    <?php if (session('error')): ?>
    <div class="wrap" style="padding-top: 16px;">
      <div class="flash flash-error"><?= e(session('error')) ?></div>
    </div>
    <?php endif; ?>
    <?php if (session('info')): ?>
    <div class="wrap" style="padding-top: 16px;">
      <div class="flash flash-info"><?= e(session('info')) ?></div>
    </div>
    <?php endif; ?>
    <?php if (isset($errors) && $errors->any()): ?>
    <div class="wrap" style="padding-top: 16px;">
      <div class="flash flash-error"><?= e($errors->first()) ?></div>
    </div>
    <?php endif; ?>
    <?= $content ?? '' ?>
  </main>

  <div class="nav-drawer-backdrop" id="navBackdrop" aria-hidden="true"></div>
  <aside class="nav-drawer" id="navDrawer" aria-label="Main menu" aria-hidden="true">
    <div class="nav-drawer-header">
      <a href="<?= e(url('/')) ?>" class="logo"><?= e(function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden') ?></a>
      <button type="button" class="nav-drawer-close" id="navDrawerClose" aria-label="Close menu"><i class="fa-solid fa-xmark"></i></button>
    </div>
    <nav class="nav-drawer-links">
      <div style="padding: 8px 0; margin-bottom: 8px; border-bottom: 1px solid var(--border);">
        <button type="button" class="theme-toggle theme-toggle-drawer" id="themeToggleDrawer" aria-label="Toggle light or dark mode" aria-pressed="false" title="Toggle theme" style="width: 100%; justify-content: flex-start; gap: 10px; padding-left: 12px;">
          <span class="theme-icon-dark" aria-hidden="true"><i class="fa-solid fa-moon"></i></span>
          <span class="theme-icon-light" aria-hidden="true"><i class="fa-solid fa-sun"></i></span>
          <span class="theme-toggle-label" id="themeToggleLabel">Switch to dark mode</span>
        </button>
      </div>
      <a href="<?= e(url('/launching-today')) ?>">Launching today</a>
      <a href="<?= e(url('/leaderboard')) ?>">Leaderboard</a>
      <a href="<?= e(url('/categories')) ?>">Categories</a>
      <a href="<?= e(url('/blog')) ?>">Blog</a>
      <a href="<?= e(url('/submit')) ?>">Submit</a>
      <a href="<?= e(url('/about')) ?>">About</a>
      <a href="<?= e(url('/contact')) ?>">Contact</a>
      <?php if (auth()->check()): ?>
      <a href="<?= e(url('/founder')) ?>" class="nav-drawer-extra"><i class="fa-solid fa-gauge-high" aria-hidden="true"></i> Dashboard</a>
      <form action="<?= e(route('logout')) ?>" method="POST" class="nav-logout-form nav-drawer-logout">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Log out</button>
      </form>
      <?php if (auth()->guard('admin')->check()): ?>
      <a href="<?= e(url('/backoffice')) ?>" class="nav-drawer-extra"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i> Admin</a>
      <?php endif; ?>
      <?php else: ?>
      <a href="<?= e(url('/login')) ?>" class="btn btn-ghost" data-modal="login">Log in</a>
      <a href="<?= e(url('/register')) ?>" class="btn btn-primary" data-modal="signup">Sign up</a>
      <?php endif; ?>
    </nav>
  </aside>

  <div class="modal-overlay" id="modalLogin" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="loginTitle">
      <div class="modal-header">
        <h2 id="loginTitle">Log in</h2>
        <button type="button" class="modal-close" aria-label="Close" data-close="modalLogin"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <?php
          $socialCreds = function_exists('gs') ? gs('socialite_credentials') : null;
          $socialProviders = [];
          if ($socialCreds && is_object($socialCreds)) {
            foreach (['google' => 'Google', 'linkedin' => 'LinkedIn', 'facebook' => 'Facebook', 'twitter' => 'Twitter'] as $key => $label) {
              if (isset($socialCreds->$key) && is_object($socialCreds->$key) && !empty($socialCreds->$key->client_id) && !empty($socialCreds->$key->client_secret) && (int)($socialCreds->$key->status ?? 0) === 1) {
                $socialProviders[$key] = $label;
              }
            }
          }
        ?>
        <?php if (!empty($socialProviders)): ?>
        <div class="social-login-wrap" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
          <?php foreach ($socialProviders as $key => $label): ?>
          <a href="<?= e(route('user.social.login', $key)) ?>" class="btn btn-ghost" style="flex: 1; min-width: 100px; justify-content: center;">
            <i class="fa-brands fa-<?= $key === 'google' ? 'google' : ($key === 'linkedin' ? 'linkedin' : ($key === 'twitter' ? 'x-twitter' : 'facebook')) ?>" aria-hidden="true"></i>
            <?= e($label) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <p class="form-hint" style="text-align: center; margin-bottom: 16px; font-size: 0.875rem; color: var(--text-muted, #64748b);">Or sign in with email</p>
        <?php endif; ?>
        <form action="<?= e(url('/login')) ?>" method="POST">
          <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
          <div class="form-group">
            <label class="form-label" for="loginEmail">Email</label>
            <input type="email" id="loginEmail" name="email" class="form-input" placeholder="you@example.com" value="<?= e(old('email')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="loginPassword">Password</label>
            <input type="password" id="loginPassword" name="password" class="form-input" placeholder="••••••••" required>
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Log in</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        Don't have an account? <a href="<?= e(url('/register')) ?>" data-switch="signup">Sign up</a>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modalSignup" aria-hidden="true">
    <div class="modal" role="dialog" aria-labelledby="signupTitle">
      <div class="modal-header">
        <h2 id="signupTitle">Sign up</h2>
        <button type="button" class="modal-close" aria-label="Close" data-close="modalSignup"><i class="fa-solid fa-xmark"></i></button>
      </div>
      <div class="modal-body">
        <?php if (!empty($socialProviders)): ?>
        <div class="social-login-wrap" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
          <?php foreach ($socialProviders as $key => $label): ?>
          <a href="<?= e(route('user.social.login', $key)) ?>" class="btn btn-ghost" style="flex: 1; min-width: 100px; justify-content: center;">
            <i class="fa-brands fa-<?= $key === 'google' ? 'google' : ($key === 'linkedin' ? 'linkedin' : ($key === 'twitter' ? 'x-twitter' : 'facebook')) ?>" aria-hidden="true"></i>
            <?= e($label) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <p class="form-hint" style="text-align: center; margin-bottom: 16px; font-size: 0.875rem; color: var(--text-muted, #64748b);">Or sign up with email</p>
        <?php endif; ?>
        <form action="<?= e(url('/register')) ?>" method="POST">
          <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
          <div class="form-group">
            <label class="form-label" for="signupName">Name</label>
            <input type="text" id="signupName" name="name" class="form-input" placeholder="Your name" value="<?= e(old('name')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="signupEmail">Email</label>
            <input type="email" id="signupEmail" name="email" class="form-input" placeholder="you@example.com" value="<?= e(old('email')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="signupPassword">Password</label>
            <input type="password" id="signupPassword" name="password" class="form-input" placeholder="At least 8 characters" required minlength="8">
          </div>
          <div class="form-group">
            <label class="form-label" for="signupPasswordConfirmation">Confirm password</label>
            <input type="password" id="signupPasswordConfirmation" name="password_confirmation" class="form-input" placeholder="At least 8 characters" required minlength="8">
          </div>
          <div class="form-actions">
            <button type="submit" class="btn btn-primary btn-block">Create account</button>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        Already have an account? <a href="<?= e(url('/login')) ?>" data-switch="login">Log in</a>
      </div>
    </div>
  </div>

  <div class="MainAdverTiseMentDiv" data-publisher="eyJpdiI6InpsbjBkRVNsSTg0YVpndEFVdCt1Mmc9PSIsInZhbHVlIjoiUnJTUHc3TzRpT3UzVWxZR3ozL0xidz09IiwibWFjIjoiMTk2MTE2YTk1YmUxZmRlZGFlMzRhNmQ2ZGRmY2E5MDBhZWQwYjk4Mjc2MDhiNmZjNmJlYTM2MjAyZDdiMDRjYiIsInRhZyI6IiJ9" data-adsize="970x90"></div>
  <script class="adScriptClass" src="https://zimadsense.com/assets/ads/ad.js"></script>

  <footer class="site-footer">
    <div class="wrap site-footer__wrap">
      <div class="site-footer__row">
        <div class="site-footer__col">
          <p class="site-footer__brand"><a href="<?= e(url('/')) ?>"><?= e(function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden') ?></a>: The place to launch and discover new tech products.</p>
          <p class="site-footer__links">
            <a href="<?= e(url('/about')) ?>">About</a>
            <a href="<?= e(url('/contact')) ?>">Contact</a>
          </p>
        </div>
        <div class="site-footer__col">
          <p class="site-footer__heading">Our other sites</p>
          <ul class="site-footer__sites">
            <li><a href="https://dextersoft.com" target="_blank" rel="noopener noreferrer">dextersoft.com</a></li>
            <li><a href="https://flipit.co.zw" target="_blank" rel="noopener noreferrer">flipit.co.zw</a></li>
            <li><a href="https://zimadsense.com" target="_blank" rel="noopener noreferrer">zimadsense.com</a></li>
          </ul>
        </div>
      </div>
      <p class="site-footer__credit">Developed with <i class="fa-solid fa-heart" aria-hidden="true" style="color: var(--accent); vertical-align: middle;"></i> by <a href="https://www.linkedin.com/in/dexterity-wurayayi-967a64230/" target="_blank" rel="noopener noreferrer">Dexter Wurayayi</a>.</p>
    </div>
  </footer>
  <style>
  .site-footer__wrap { display: flex; flex-direction: column; gap: 1.5rem; }
  .site-footer__row { display: flex; flex-wrap: wrap; gap: 2rem; align-items: flex-start; }
  .site-footer__col { flex: 1 1 200px; }
  .site-footer__brand { margin: 0 0 0.25rem; }
  .site-footer__links { margin: 0; }
  .site-footer__links a + a { margin-left: 0.5rem; }
  .site-footer__heading { font-weight: 700; font-size: 0.9375rem; margin: 0 0 0.5rem; color: var(--text); }
  .site-footer__sites { list-style: none; margin: 0; padding: 0; }
  .site-footer__sites li { margin: 0.25rem 0; }
  .site-footer__sites a { text-decoration: none; }
  .site-footer__sites a:hover { text-decoration: underline; }
  .site-footer__credit { margin: 0; padding-top: 1rem; border-top: 1px solid var(--border); text-align: center; font-size: 0.875rem; color: var(--text-muted); }
  .site-footer__credit a { color: var(--link); }
  .site-footer__credit a:hover { color: var(--link-hover); }
  </style>

  <script>
    (function() {
      function getTheme() {
        return document.documentElement.getAttribute('data-theme') || 'light';
      }
      function setTheme(theme) {
        theme = theme || 'light';
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem('eden_theme', theme); } catch (e) {}
        var isDark = theme === 'dark';
        document.querySelectorAll('.theme-toggle').forEach(function(btn) {
          if (btn) btn.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        });
        var label = document.getElementById('themeToggleLabel');
        if (label) label.textContent = isDark ? 'Switch to light mode' : 'Switch to dark mode';
      }
      function toggleTheme() {
        setTheme(getTheme() === 'dark' ? 'light' : 'dark');
      }
      document.getElementById('themeToggle')?.addEventListener('click', toggleTheme);
      document.getElementById('themeToggleDrawer')?.addEventListener('click', toggleTheme);
      var t = getTheme();
      document.querySelectorAll('.theme-toggle').forEach(function(btn) {
        if (btn) btn.setAttribute('aria-pressed', t === 'dark' ? 'true' : 'false');
      });
      var lbl = document.getElementById('themeToggleLabel');
      if (lbl) lbl.textContent = t === 'dark' ? 'Switch to light mode' : 'Switch to dark mode';
    })();
    (function() {
      var navToggle = document.getElementById('navToggle');
      var navDrawer = document.getElementById('navDrawer');
      var navDrawerClose = document.getElementById('navDrawerClose');
      var navBackdrop = document.getElementById('navBackdrop');
      function openNav() {
        if (navDrawer) navDrawer.classList.add('is-open');
        if (navBackdrop) navBackdrop.classList.add('is-open');
        document.body.style.overflow = 'hidden';
        if (navDrawer) navDrawer.setAttribute('aria-hidden', 'false');
        if (navBackdrop) navBackdrop.setAttribute('aria-hidden', 'false');
      }
      function closeNav() {
        if (navDrawer) navDrawer.classList.remove('is-open');
        if (navBackdrop) navBackdrop.classList.remove('is-open');
        document.body.style.overflow = '';
        if (navDrawer) navDrawer.setAttribute('aria-hidden', 'true');
        if (navBackdrop) navBackdrop.setAttribute('aria-hidden', 'true');
      }
      if (navToggle && navDrawer) {
        navToggle.addEventListener('click', function() { openNav(); });
      }
      if (navDrawerClose) navDrawerClose.addEventListener('click', closeNav);
      if (navBackdrop) navBackdrop.addEventListener('click', closeNav);
      if (navDrawer) {
        navDrawer.querySelectorAll('a').forEach(function(link) {
          link.addEventListener('click', closeNav);
        });
      }
      document.querySelectorAll('[data-modal]').forEach(function(el) {
        el.addEventListener('click', function(e) {
          e.preventDefault();
          var id = 'modal' + (this.getAttribute('data-modal') === 'login' ? 'Login' : 'Signup');
          var modal = document.getElementById(id);
          if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
          }
        });
      });
      document.querySelectorAll('[data-close]').forEach(function(el) {
        el.addEventListener('click', function() {
          var modal = document.getElementById(this.getAttribute('data-close'));
          if (modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
          }
        });
      });
      document.querySelectorAll('[data-switch]').forEach(function(el) {
        el.addEventListener('click', function(e) {
          e.preventDefault();
          document.querySelectorAll('.modal-overlay').forEach(function(m) {
            m.classList.remove('is-open');
            m.setAttribute('aria-hidden', 'true');
          });
          var id = 'modal' + (this.getAttribute('data-switch') === 'login' ? 'Login' : 'Signup');
          var modal = document.getElementById(id);
          if (modal) {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
          }
        });
      });
      document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
          if (e.target === overlay) {
            overlay.classList.remove('is-open');
            overlay.setAttribute('aria-hidden', 'true');
          }
        });
      });
    })();
  </script>
  <?= $scripts ?? '' ?>
</body>
</html>

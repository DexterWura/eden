<!DOCTYPE html>
<html lang="en" itemscope itemtype="https://schema.org/WebPage">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php
    $siteName = function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden';
    $pageTitleFinal = isset($pageTitle) ? $pageTitle : (isset($title) ? $title . ' | ' . $siteName : $siteName . ' | Startup Directory');
    $metaDesc = isset($metaDescription) ? $metaDescription : (function_exists('gs') && gs('meta_description') ? gs('meta_description') : (function_exists('gs') && gs('social_description') ? gs('social_description') : 'Startup directory for discoverability and growth.'));
    $socialDesc = isset($metaDescription) ? $metaDescription : (function_exists('gs') && gs('social_description') ? gs('social_description') : $metaDesc);
    $metaKeywordsFinal = isset($metaKeywords) ? $metaKeywords : (function_exists('gs') && gs('meta_keywords') ? gs('meta_keywords') : '');
    $seoImageUrl = isset($metaImage) ? $metaImage : (function_exists('gs') && gs('seo_image') ? url(asset(gs('seo_image'))) : '');
    $canonicalUrl = isset($canonicalUrl) ? $canonicalUrl : url()->current();
    $metaRobots = isset($metaRobots) ? $metaRobots : null;
    $ogType = isset($ogType) ? $ogType : 'website';
    $ogImageAlt = isset($ogImageAlt) ? $ogImageAlt : ($siteName . ' – Startup directory');
    $baseUrl = rtrim(url('/'), '/');
    $hasSearchQuery = request()->filled('q') && trim((string) request()->query('q')) !== '';
    $includeDefaultSiteGraph = isset($includeDefaultSiteGraph)
      ? (bool) $includeDefaultSiteGraph
      : (request()->path() === '' && ! $hasSearchQuery && (int) request()->query('page', 1) <= 1);
  ?>
  <title><?= e($pageTitleFinal) ?></title>
  <link rel="icon" type="image/png" href="<?= e(asset('images/favicon.png')) ?>">
  <script>
    (function(){var t=localStorage.getItem('eden_theme')||'light';document.documentElement.setAttribute('data-theme',t);})();
  </script>
  <link rel="canonical" href="<?= e($canonicalUrl) ?>">
  <?php if ($metaRobots !== null && $metaRobots !== ''): ?><meta name="robots" content="<?= e($metaRobots) ?>"><?php endif; ?>
  <?php if ($metaKeywordsFinal !== ''): ?><meta name="keywords" content="<?= e($metaKeywordsFinal) ?>"><?php endif; ?>
  <meta name="description" content="<?= e($metaDesc) ?>">
  <meta property="og:type" content="<?= e($ogType) ?>">
  <meta property="og:url" content="<?= e($canonicalUrl) ?>">
  <meta property="og:title" content="<?= e($pageTitleFinal) ?>">
  <meta property="og:description" content="<?= e($socialDesc) ?>">
  <meta property="og:site_name" content="<?= e($siteName) ?>">
  <meta property="og:locale" content="en_US">
  <?php if ($seoImageUrl): ?>
  <meta property="og:image" content="<?= e($seoImageUrl) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:image:alt" content="<?= e($ogImageAlt) ?>">
  <?php endif; ?>
  <meta name="twitter:card" content="<?= $seoImageUrl ? 'summary_large_image' : 'summary' ?>">
  <meta name="twitter:title" content="<?= e($pageTitleFinal) ?>">
  <meta name="twitter:description" content="<?= e($socialDesc) ?>">
  <?php if ($seoImageUrl): ?><meta name="twitter:image" content="<?= e($seoImageUrl) ?>"><?php endif; ?>
  <?php
    $siteSchema = [
      '@context' => 'https://schema.org',
      '@graph' => [
        [
          '@type' => 'Organization',
          '@id' => $baseUrl . '/#organization',
          'name' => $siteName,
          'url' => $baseUrl . '/',
          'logo' => ['@type' => 'ImageObject', 'url' => url(asset('images/favicon.png'))],
        ],
        [
          '@type' => 'WebSite',
          '@id' => $baseUrl . '/#website',
          'url' => $baseUrl . '/',
          'name' => $siteName,
          'description' => $metaDesc,
          'publisher' => ['@id' => $baseUrl . '/#organization'],
          'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => ['@type' => 'EntryPoint', 'urlTemplate' => $baseUrl . '/?q={search_term_string}'],
            'query-input' => 'required name=search_term_string',
          ],
        ],
      ],
    ];
    $organizationOnlySchema = [
      '@context' => 'https://schema.org',
      '@type' => 'Organization',
      '@id' => $baseUrl . '/#organization',
      'name' => $siteName,
      'url' => $baseUrl . '/',
      'logo' => ['@type' => 'ImageObject', 'url' => url(asset('images/favicon.png'))],
    ];
    $hasPageStructuredData = isset($structuredData) && is_array($structuredData) && !empty($structuredData);
    $allStructured = [];
    if ($includeDefaultSiteGraph) {
      $allStructured[] = $siteSchema;
    } elseif (! $hasPageStructuredData) {
      $allStructured[] = $organizationOnlySchema;
    }
    if ($hasPageStructuredData) {
      foreach ((isset($structuredData[0]) && is_array($structuredData[0]) ? $structuredData : [$structuredData]) as $sd) {
        $allStructured[] = $sd;
      }
    }
  ?>
  <?php if (!empty($allStructured)): ?>
  <script type="application/ld+json"><?= json_encode($allStructured, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
  <?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  <?php $cssPath = public_path('css/main.css'); $cssVersion = file_exists($cssPath) ? substr(md5_file($cssPath), 0, 12) : ''; ?>
  <link rel="stylesheet" href="<?= e(asset('css/main.css')) ?><?= $cssVersion ? '?v=' . $cssVersion : '' ?>">
  <?php
  $adsenseEnabled = function_exists('gs') && (bool) gs('adsense_enabled');
  $adsenseScript = $adsenseEnabled ? trim((string)(gs('adsense_script') ?? '')) : '';
  $advertisingConsent = request()->cookie('eden_ad_consent') === 'granted';
  ?>
  <?php if ($adsenseEnabled): ?>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('consent', 'default', {
      ad_storage: 'denied',
      ad_user_data: 'denied',
      ad_personalization: 'denied',
      analytics_storage: 'denied',
      wait_for_update: 500
    });
    <?php if ($advertisingConsent): ?>
    gtag('consent', 'update', {
      ad_storage: 'granted',
      ad_user_data: 'granted',
      ad_personalization: 'granted'
    });
    <?php endif; ?>
  </script>
  <?php endif; ?>
  <?php echo $adsenseScript !== '' && $advertisingConsent ? "\n  " . $adsenseScript . "\n" : ''; ?>
</head>
<body>
  <div class="bg-grid"></div>
  <div class="bg-glow"></div>

  <div id="cookieConsent" class="cookie-consent" role="dialog" aria-label="Cookie notice" aria-live="polite" hidden>
    <div class="cookie-consent-inner wrap">
      <p class="cookie-consent-text">We use necessary cookies to run Eden. With your permission, advertising cookies may also be used. Read our <a href="<?= e(url('/privacy')) ?>">Privacy Policy</a>.</p>
      <div class="cookie-consent-actions">
        <button type="button" class="btn btn-ghost cookie-consent-btn" id="cookieConsentReject">Necessary only</button>
        <button type="button" class="btn btn-primary cookie-consent-btn" id="cookieConsentAccept">Allow advertising</button>
      </div>
    </div>
  </div>

  <div id="edenToastContainer" class="eden-toast-container" aria-live="polite"></div>

  <button type="button" id="backToTop" class="back-to-top" aria-label="Back to top" title="Back to top" hidden>
    <i class="fa-solid fa-arrow-up" aria-hidden="true"></i>
  </button>

  <header class="site-header">
    <div class="wrap header-inner">
      <a href="<?= e(url('/')) ?>" class="logo"><?= e(function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden') ?></a>
      <form action="<?= e(url('/')) ?>" method="get" class="header-search" role="search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" name="q" value="<?= e(request()->query('q', '')) ?>" placeholder="Search startups" aria-label="Search startups">
      </form>
      <nav class="nav-main">
        <button type="button" class="theme-toggle" id="themeToggle" aria-label="Toggle light or dark mode" aria-pressed="false" title="Toggle theme">
          <span class="theme-icon-dark" aria-hidden="true"><i class="fa-solid fa-moon"></i></span>
          <span class="theme-icon-light" aria-hidden="true"><i class="fa-solid fa-sun"></i></span>
        </button>
        <a href="<?= e(url('/launching-today')) ?>">Launches</a>
        <a href="<?= e(url('/leaderboard')) ?>">Leaderboard</a>
        <a href="<?= e(url('/categories')) ?>">Categories</a>
        <div class="nav-more">
          <button type="button" class="nav-more-trigger" aria-expanded="false">More <i class="fa-solid fa-chevron-down" aria-hidden="true"></i></button>
          <div class="nav-more-menu">
            <a href="<?= e(url('/raising')) ?>">Raising</a>
            <a href="<?= e(url('/for-sale')) ?>">For sale</a>
            <a href="<?= e(url('/blog')) ?>">Blog</a>
            <a href="<?= e(url('/pricing')) ?>"><i class="fa-solid fa-crown" aria-hidden="true"></i> Pro</a>
          </div>
        </div>
        <a href="<?= e(url('/submit')) ?>" class="btn btn-primary nav-submit"><i class="fa-solid fa-plus" aria-hidden="true"></i> Submit</a>
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
      <a href="<?= e(url('/raising')) ?>">Raising</a>
      <a href="<?= e(url('/for-sale')) ?>">For sale</a>
      <a href="<?= e(url('/blog')) ?>">Blog</a>
      <a href="<?= e(url('/submit')) ?>">Submit</a>
      <a href="<?= e(url('/pricing')) ?>" style="color:var(--accent)"><i class="fa-solid fa-crown" aria-hidden="true"></i> Pro</a>
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
            foreach (['google' => 'Google', 'facebook' => 'Facebook', 'twitter' => 'Twitter'] as $key => $label) {
              if (isset($socialCreds->$key) && is_object($socialCreds->$key) && !empty($socialCreds->$key->client_id) && !empty($socialCreds->$key->client_secret) && (int)($socialCreds->$key->status ?? 0) === 1) {
                $socialProviders[$key] = $label;
              }
            }
          }
          $linkedinConfigured = \App\Http\Controllers\Eden\LinkedInAuthController::isConfigured();
        ?>
        <?php if ($linkedinConfigured): ?>
        <a href="<?= e(url('/auth/linkedin')) ?>" class="btn btn-ghost btn-block btn-linkedin" style="margin-bottom: 14px; justify-content: center; gap: 8px;">
          <i class="fa-brands fa-linkedin" aria-hidden="true"></i> Continue with LinkedIn
        </a>
        <?php endif; ?>
        <?php if (!empty($socialProviders)): ?>
        <div class="social-login-wrap" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
          <?php foreach ($socialProviders as $key => $label): ?>
          <a href="<?= e(route('user.social.login', $key)) ?>" class="btn btn-ghost" style="flex: 1; min-width: 100px; justify-content: center;">
            <i class="fa-brands fa-<?= $key === 'google' ? 'google' : ($key === 'twitter' ? 'x-twitter' : 'facebook') ?>" aria-hidden="true"></i>
            <?= e($label) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($linkedinConfigured || !empty($socialProviders)): ?>
        <div class="auth-divider"><span>or sign in with email</span></div>
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
        <?php if ($linkedinConfigured): ?>
        <a href="<?= e(url('/auth/linkedin')) ?>" class="btn btn-ghost btn-block btn-linkedin" style="margin-bottom: 14px; justify-content: center; gap: 8px;">
          <i class="fa-brands fa-linkedin" aria-hidden="true"></i> Continue with LinkedIn
        </a>
        <?php endif; ?>
        <?php if (!empty($socialProviders)): ?>
        <div class="social-login-wrap" style="display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 16px;">
          <?php foreach ($socialProviders as $key => $label): ?>
          <a href="<?= e(route('user.social.login', $key)) ?>" class="btn btn-ghost" style="flex: 1; min-width: 100px; justify-content: center;">
            <i class="fa-brands fa-<?= $key === 'google' ? 'google' : ($key === 'twitter' ? 'x-twitter' : 'facebook') ?>" aria-hidden="true"></i>
            <?= e($label) ?>
          </a>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <?php if ($linkedinConfigured || !empty($socialProviders)): ?>
        <div class="auth-divider"><span>or sign up with email</span></div>
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

  <footer class="site-footer">
    <div class="wrap site-footer__wrap">
      <div class="site-footer__row">
        <div class="site-footer__col">
          <p class="site-footer__brand"><a href="<?= e(url('/')) ?>"><?= e(function_exists('gs') && gs('site_name') ? gs('site_name') : 'Eden') ?></a></p>
          <p class="site-footer__tagline">Discover and launch startups without the noise.</p>
          <p class="site-footer__links site-footer__links--primary">
            <a href="<?= e(url('/about')) ?>">About</a>
            <a href="<?= e(url('/contact')) ?>">Contact</a>
            <a href="<?= e(url('/categories')) ?>">Categories</a>
            <a href="<?= e(url('/blog')) ?>">Blog</a>
          </p>
          <p class="site-footer__links site-footer__links--secondary">
            <a href="<?= e(url('/privacy')) ?>">Privacy</a>
            <a href="<?= e(url('/terms')) ?>">Terms</a>
            <?php if (auth()->check()): ?><a href="<?= e(url('/saved')) ?>">Saved</a><?php endif; ?>
          </p>
        </div>
        <div class="site-footer__col">
          <?php include __DIR__ . '/partials/sister-sites.php'; ?>
        </div>
      </div>
      <p class="site-footer__credit">Built by <a href="https://www.linkedin.com/in/dexterity-wurayayi-967a64230/" target="_blank" rel="noopener noreferrer">Dexter Wurayayi</a>.</p>
    </div>
  </footer>
  <style>
  .site-footer { padding: 28px 0; }
  .site-footer__wrap { display: flex; flex-direction: column; gap: 1.25rem; }
  .site-footer__row { display: grid; grid-template-columns: 1.4fr 1fr; gap: 1.5rem; align-items: start; }
  .site-footer__col { min-width: 0; }
  .site-footer__brand { margin: 0; font-size: 1rem; font-weight: 700; color: var(--text); }
  .site-footer__brand a { color: inherit; text-decoration: none; }
  .site-footer__tagline { margin: 0.25rem 0 0.75rem; color: var(--text-muted); font-size: 0.9rem; max-width: 36ch; }
  .site-footer__links { margin: 0; display: flex; flex-wrap: wrap; gap: 0.45rem 0.8rem; }
  .site-footer__links + .site-footer__links { margin-top: 0.45rem; }
  .site-footer__links a { color: var(--link); text-decoration: none; font-size: 0.88rem; }
  .site-footer__links a:hover { color: var(--link-hover); text-decoration: underline; }
  .site-footer__heading { font-weight: 600; font-size: 0.9rem; margin: 0 0 0.5rem; color: var(--text); }
  .site-footer__sites { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 0.45rem; }
  .site-footer__sites li { margin: 0; }
  .site-footer__sites a { text-decoration: none; }
  .site-footer__sites a:hover { text-decoration: underline; }
  .site-footer__credit { margin: 0; padding-top: 0.85rem; border-top: 1px solid var(--border); text-align: center; font-size: 0.82rem; color: var(--text-muted); }
  .site-footer__credit a { color: var(--link); }
  .site-footer__credit a:hover { color: var(--link-hover); }
  @media (max-width: 800px) {
    .site-footer__row { grid-template-columns: 1fr; gap: 1rem; }
  }
  .cookie-consent { position: fixed; bottom: 0; left: 0; right: 0; z-index: 9999; background: var(--surface, #12141c); border-top: 1px solid var(--border, #2a2e3d); padding: 12px 0; box-shadow: 0 -4px 20px rgba(0,0,0,0.2); }
  .cookie-consent[hidden] { display: none !important; }
  .cookie-consent-inner { display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; }
  .cookie-consent-text { margin: 0; font-size: 0.9rem; color: var(--text-muted, #8b90a0); }
  .cookie-consent-text a { color: var(--accent, #00d4aa); text-decoration: underline; }
  .cookie-consent-btn { flex-shrink: 0; }
  .eden-toast-container { position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%); z-index: 9998; display: flex; flex-direction: column; gap: 8px; max-width: min(400px, calc(100vw - 32px)); pointer-events: none; }
  .eden-toast { pointer-events: auto; padding: 12px 16px; border-radius: var(--radius-sm, 8px); font-size: 0.9rem; line-height: 1.4; box-shadow: 0 4px 20px rgba(0,0,0,0.2); border: 1px solid var(--border); background: var(--surface); color: var(--text); animation: eden-toast-in 0.25s ease; }
  .eden-toast--success { border-left: 4px solid var(--accent); }
  .eden-toast--error { border-left: 4px solid var(--danger, #ff4767); }
  .eden-toast--info { border-left: 4px solid var(--text-muted); }
  .eden-toast--promo { border-left: 4px solid var(--accent); }
  .eden-toast--promo .eden-toast-cta { display: inline-block; margin-top: 8px; font-weight: 600; color: var(--accent); text-decoration: none; font-size: 0.875rem; }
  .eden-toast--promo .eden-toast-cta:hover { text-decoration: underline; }
  @keyframes eden-toast-in { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
  .back-to-top { position: fixed; bottom: 24px; right: 24px; z-index: 9990; width: 48px; height: 48px; border-radius: 50%; border: 1px solid var(--border); background: var(--surface); color: var(--accent); box-shadow: 0 4px 20px rgba(0,0,0,0.15); cursor: pointer; display: flex; align-items: center; justify-content: center; transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s; opacity: 0; pointer-events: none; }
  .back-to-top[hidden] { display: none !important; }
  .back-to-top.is-visible { opacity: 1; pointer-events: auto; }
  .back-to-top:hover { background: var(--surface-hover); box-shadow: 0 6px 24px rgba(0,0,0,0.2); transform: translateY(-2px); }
  .back-to-top:active { transform: translateY(0); }
  .back-to-top i { font-size: 1.125rem; }
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
      var banner = document.getElementById('cookieConsent');
      var accept = document.getElementById('cookieConsentAccept');
      var reject = document.getElementById('cookieConsentReject');
      var current = document.cookie.match(/(?:^|;\s*)eden_ad_consent=([^;]+)/);
      if (banner && !current) banner.removeAttribute('hidden');
      function saveConsent(value) {
        var secure = window.location.protocol === 'https:' ? '; Secure' : '';
        document.cookie = 'eden_ad_consent=' + value + '; Max-Age=31536000; Path=/; SameSite=Lax' + secure;
        if (banner) banner.setAttribute('hidden', '');
        if (value === 'granted') window.location.reload();
      }
      if (accept) accept.addEventListener('click', function() { saveConsent('granted'); });
      if (reject) reject.addEventListener('click', function() { saveConsent('denied'); });
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
      document.querySelectorAll('.nav-more-trigger').forEach(function(trigger) {
        trigger.addEventListener('click', function(e) {
          e.stopPropagation();
          var open = this.getAttribute('aria-expanded') === 'true';
          this.setAttribute('aria-expanded', open ? 'false' : 'true');
          this.parentElement.classList.toggle('is-open', !open);
        });
      });
      document.addEventListener('click', function() {
        document.querySelectorAll('.nav-more.is-open').forEach(function(menu) {
          menu.classList.remove('is-open');
          var trigger = menu.querySelector('.nav-more-trigger');
          if (trigger) trigger.setAttribute('aria-expanded', 'false');
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
    (function() {
      var backToTop = document.getElementById('backToTop');
      if (backToTop) {
        var scrollThreshold = 400;
        function updateVisibility() {
          if (window.scrollY > scrollThreshold) {
            backToTop.removeAttribute('hidden');
            backToTop.classList.add('is-visible');
          } else {
            backToTop.classList.remove('is-visible');
            backToTop.setAttribute('hidden', '');
          }
        }
        window.addEventListener('scroll', function() { updateVisibility(); }, { passive: true });
        updateVisibility();
        backToTop.addEventListener('click', function() {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });
      }
    })();
    (function() {
      var container = document.getElementById('edenToastContainer');
      if (!container) return;
      var loginUrl = '<?= e(url('/login')) ?>';
      var signupUrl = '<?= e(url('/register')) ?>';
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
      window.notify = function(type, msg) {
        if (typeof msg !== 'string') msg = (msg && msg[0]) ? msg[0] : 'Done';
        showToast(type || 'info', msg, {});
      };
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
      window.edenLoginUrl = loginUrl;
      window.edenSignupUrl = signupUrl;
      window.edenPricingUrl = pricingUrl;
      document.addEventListener('click', function(e) {
        var btn = e.target.closest('.eden-guest-save');
        if (btn) {
          e.preventDefault();
          e.stopPropagation();
          if (typeof edenPromoToast === 'function') {
            edenPromoToast({ message: 'Sign in to save startups and get personalized recommendations.', ctaText: 'Log in', ctaHref: loginUrl });
          }
        }
      });
    })();
  </script>
  <?= $scripts ?? '' ?>
</body>
</html>

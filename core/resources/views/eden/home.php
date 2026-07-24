<section class="hero">
  <div class="hero-bg" aria-hidden="true">
    <span class="hero-bg-grid"></span>
  </div>
  <div class="wrap hero-inner">
    <span class="hero-kicker hero-reveal hero-reveal--1">Explore what builders are shipping</span>
    <h1 class="hero-reveal hero-reveal--1">Don't Miss the Next Slack: These SaaS Products Are About to Blow Up.</h1>
    <p class="hero-reveal hero-reveal--2">Be the first to try rising tools before they go mainstream.</p>
    <div class="hero-actions hero-reveal hero-reveal--3">
      <form action="<?= e(url('/')) ?>" method="get" class="hero-search" role="search">
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
        <input type="search" name="q" class="search-input" placeholder="Search SaaS products" aria-label="Search SaaS products" id="homeSearch" value="<?= e($searchQuery ?? '') ?>">
        <?php if (isset($categoryFilter) && $categoryFilter !== null && $categoryFilter !== ''): ?><input type="hidden" name="category" value="<?= e($categoryFilter) ?>"><?php endif; ?>
        <button type="submit" class="hero-search-submit" aria-label="Search SaaS products"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
      </form>
      <a href="<?= e(url('/submit')) ?>" class="btn btn-add"><i class="fa-solid fa-plus" aria-hidden="true"></i> Submit your product</a>
    </div>
    <svg class="hero-accent hero-accent--spark" viewBox="0 0 92 78" aria-hidden="true">
      <path d="M18 42 8 24M42 34 39 8M60 43 77 25M65 59 88 60" />
    </svg>
    <?php
    $showTrustedByBlock = $showTrustedByBlock ?? false;
    $featuredFounders = $featuredFounders ?? collect();
    if ($showTrustedByBlock && $featuredFounders->isNotEmpty()):
    ?>
    <div class="hero-trusted-by hero-reveal hero-reveal--4" aria-label="Trusted by founders">
      <svg class="hero-accent hero-accent--squiggle" viewBox="0 0 150 76" aria-hidden="true">
        <path d="M5 55c17 12 20-38 37-29 11 6 2 37 15 39 15 3 24-39 40-35 12 3 9 30 24 30 11 0 18-12 24-22" />
      </svg>
      <div class="hero-trusted-by-avatars">
        <?php foreach ($featuredFounders as $founder):
          $photoUrl = !empty(trim($founder->hero_photo_url ?? '')) ? $founder->hero_photo_url : null;
          $linkedinUrl = !empty(trim($founder->hero_linkedin_url ?? '')) ? $founder->hero_linkedin_url : null;
          $name = $founder->name ?? 'Founder';
          $initials = preg_match('/\S+\s+(\S)/', $name, $m) ? strtoupper(mb_substr($name, 0, 1) . $m[1]) : strtoupper(mb_substr($name, 0, 2));
          if ($initials === '') $initials = '?';
        ?>
        <?php
          $isExternal = $photoUrl && (str_starts_with($photoUrl, 'http://') || str_starts_with($photoUrl, 'https://'));
          $imgSrc = $photoUrl ? ($isExternal ? $photoUrl : asset($photoUrl)) : null;
        ?>
        <?php if ($linkedinUrl): ?>
        <a href="<?= e($linkedinUrl) ?>" target="_blank" rel="noopener" class="hero-trusted-by-avatar" title="<?= e($name) ?>">
          <?php if ($imgSrc): ?>
          <img src="<?= e($imgSrc) ?>" alt="<?= e($name) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display=''">
          <span class="hero-trusted-by-initials" style="display:none"><?= e($initials) ?></span>
          <?php else: ?>
          <span class="hero-trusted-by-initials"><?= e($initials) ?></span>
          <?php endif; ?>
        </a>
        <?php else: ?>
        <div class="hero-trusted-by-avatar" title="<?= e($name) ?>">
          <?php if ($imgSrc): ?>
          <img src="<?= e($imgSrc) ?>" alt="<?= e($name) ?>" onerror="this.style.display='none';this.nextElementSibling.style.display=''">
          <span class="hero-trusted-by-initials" style="display:none"><?= e($initials) ?></span>
          <?php else: ?>
          <span class="hero-trusted-by-initials"><?= e($initials) ?></span>
          <?php endif; ?>
        </div>
        <?php endif; ?>
        <?php endforeach; ?>
      </div>
      <p class="hero-trusted-by-text">100+ founders</p>
    </div>
    <?php endif; ?>
    <?php $browseCategories = $browseCategories ?? []; ?>
    <?php
    $categoryFilter = $categoryFilter ?? null;
    $featuredOnly = $featuredOnly ?? false;
    $sortNewest = $sortNewest ?? false;
    $searchQuery = $searchQuery ?? null;
    ?>
  </div>
</section>

<div class="wrap discovery-layout">
  <div class="discovery-main">
  <?php $sortNewest = $sortNewest ?? false; ?>
  <section class="section-block" aria-labelledby="startups-heading">
    <header class="section-header">
      <div>
        <h2 id="startups-heading" class="section-heading"><?= ($searchQuery ?? '') !== '' ? 'Search results for “' . e($searchQuery) . '”' : (($sortNewest ?? false) ? 'Newest apps' : (($featuredOnly ?? false) ? 'Featured apps' : 'All apps')) ?></h2>
        <?php if (($searchQuery ?? '') === '' && !($featuredOnly ?? false)): ?><p class="section-sub">Most upvoted apps first, with recent listings breaking ties.</p><?php endif; ?>
      </div>
      <?php if (($searchQuery ?? '') !== ''): ?>
      <a href="<?= e(url('/')) ?>" class="section-link-all">Clear search <i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a>
      <?php endif; ?>
    </header>
    <?php
    $hasAlertFilters = (isset($searchQuery) && trim((string) $searchQuery) !== '')
        || (isset($categoryFilter) && $categoryFilter !== null && $categoryFilter !== '');
    ?>
    <?php if ($hasAlertFilters): ?>
    <div class="search-alert-callout" style="margin-bottom: 20px; padding: 16px 18px; border: 1px solid var(--border, #e2e8f0); border-radius: 10px; background: var(--surface, rgba(15,23,42,0.2));">
      <p style="margin: 0 0 12px; font-size: 0.9rem; line-height: 1.5; color: var(--text-muted, #64748b);">Get an email when new listings match these filters (weekly).</p>
      <?php if (isset($errors) && ($errors->has('email') || $errors->has('search_query'))): ?>
      <p style="color: #b91c1c; font-size: 0.875rem; margin: 0 0 10px;"><?= e($errors->first('email') ?: $errors->first('search_query')) ?></p>
      <?php endif; ?>
      <form action="<?= e(route('search-alerts.store')) ?>" method="post" class="search-alert-form" style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="search_query" value="<?= e($searchQuery ?? '') ?>">
        <input type="hidden" name="category" value="<?= e($categoryFilter ?? '') ?>">
        <div style="position:absolute;left:-9999px;width:1px;height:1px;overflow:hidden;" aria-hidden="true">
          <label for="searchAlertHp">Website</label>
          <input type="text" name="website" id="searchAlertHp" tabindex="-1" autocomplete="off">
        </div>
        <label for="searchAlertEmail" class="visually-hidden">Email</label>
        <input type="email" name="email" id="searchAlertEmail" required placeholder="you@example.com" class="form-input" style="min-width: 200px; max-width: 100%;" value="<?= e(old('email', auth()->check() ? auth()->user()->email : '')) ?>">
        <button type="submit" class="btn btn-ghost"><i class="fa-solid fa-bell" aria-hidden="true"></i> Email me new matches</button>
      </form>
    </div>
    <?php endif; ?>
    <div class="startup-list" id="startups">
      <?php
      $allStartups = $allStartups ?? collect();
      foreach ($allStartups as $startup):
        $rank = null;
        $showRank = false;
        $cardVariant = 'feed';
        include __DIR__ . '/_startup-card.php';
      endforeach;
      ?>
      <?php if ($allStartups instanceof \Illuminate\Support\Collection && $allStartups->isEmpty()): ?>
      <p class="section-empty"><?= ($searchQuery ?? '') !== '' ? 'No apps match your search. Try different keywords.' : 'No apps yet. <a href="' . e(url('/submit')) . '">Submit your app</a>.' ?></p>
      <?php endif; ?>
    </div>
    <?php if ($allStartups instanceof \Illuminate\Contracts\Pagination\Paginator && $allStartups->hasPages()): ?>
    <div class="pagination-container" style="margin-top: 24px;"><?= $allStartups->withQueryString()->links() ?></div>
    <?php endif; ?>
  </section>
  </div>

  <aside class="discovery-sidebar" aria-label="Discover more">
    <?php if (count($browseCategories) > 0): ?>
    <section class="sidebar-panel">
      <div class="sidebar-panel-head">
        <h2>Explore categories</h2>
        <a href="<?= e(url('/categories')) ?>">All</a>
      </div>
      <div class="sidebar-categories">
        <?php foreach (array_slice(is_array($browseCategories) ? $browseCategories : $browseCategories->all(), 0, 8) as $cat): ?>
        <a href="<?= e(url('/categories/' . \Illuminate\Support\Str::slug($cat->name))) ?>"><?= e($cat->name) ?></a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <?php if ($featuredStartups->isNotEmpty()): ?>
    <section class="sidebar-panel">
      <div class="sidebar-panel-head">
        <h2>Featured apps</h2>
      </div>
      <div class="sidebar-featured-startups">
        <?php foreach ($featuredStartups as $startup): ?>
        <?php
          $featuredLogoPath = $startup->logo_path ?? null;
          $featuredLogoLetters = $startup->logo_letters ?? strtoupper(mb_substr($startup->name, 0, 2));
          $featuredBlurb = $startup->tagline ?: ($startup->short_description ?? '');
        ?>
        <a href="<?= e(url('/startup/' . $startup->slug)) ?>" class="sidebar-featured-startup">
          <span class="sidebar-featured-logo" aria-hidden="true">
            <?php if ($featuredLogoPath): ?>
            <img src="<?= e(asset($featuredLogoPath)) ?>" alt="" width="36" height="36" loading="lazy" decoding="async">
            <?php else: ?>
            <?= e($featuredLogoLetters) ?>
            <?php endif; ?>
          </span>
          <span class="sidebar-featured-copy">
            <strong><?= e($startup->name) ?></strong>
            <?php if ($featuredBlurb !== ''): ?>
            <small><?= e(\Illuminate\Support\Str::limit($featuredBlurb, 64)) ?></small>
            <?php endif; ?>
          </span>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>

    <section class="sidebar-panel sidebar-newsletter">
      <span class="sidebar-eyebrow">Weekly digest</span>
      <h2>What Zimbabwe is building</h2>
      <p>New launches and founder stories, once a week.</p>
      <form action="<?= e(url('/subscribe')) ?>" method="POST">
        <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
        <label class="visually-hidden" for="sidebarEmail">Email address</label>
        <input id="sidebarEmail" type="email" name="email" placeholder="you@example.com" required>
        <button type="submit" class="btn btn-primary">Subscribe</button>
      </form>
    </section>

    <section class="sidebar-panel sidebar-sponsored" aria-label="Sponsored">
      <span class="sponsored-label">Sponsored</span>
      <?php
        $ad = $homeSidebarAd ?? null;
        $buyUrl = url('/advertise/home-sidebar');
        $emptyTitle = 'Advertise here';
        $emptyCopy = 'Put your product in front of founders, builders and investors for one month.';
        $maxWidth = 320;
        $showEmptyPromotion = true;
        include __DIR__ . '/partials/ad-spot.php';
      ?>
    </section>
  </aside>
</div>

  <?php $homeBottomAd = $homeBottomAd ?? null; ?>
  <div class="wrap home-ad-spot home-ad-spot--bottom" style="margin-top: 24px; margin-bottom: 32px;">
    <?php
      $ad = $homeBottomAd;
      $buyUrl = url('/advertise/home-bottom');
      $emptyTitle = 'Ad spot for sale';
      $emptyCopy = '728×90 banner above the footer call to action.';
      $maxWidth = 728;
      include __DIR__ . '/partials/ad-spot.php';
    ?>
  </div>

  <div class="wrap cta-strip" id="submit">
    <h3>Launching something?</h3>
    <p>Get your app in front of investors and customers. Submit in under 2 minutes.</p>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your app</a>
    <a href="<?= e(url('/about')) ?>" class="btn btn-ghost">View guidelines</a>
  </div>

  <div class="wrap newsletter">
    <form action="<?= e(url('/subscribe')) ?>" method="POST" class="newsletter-form">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
      <input type="email" name="email" placeholder="Your email" aria-label="Email" required>
      <button type="submit" class="btn btn-primary">Subscribe</button>
    </form>
    <p class="newsletter-note">Stay updated on new apps. No spam.</p>
  </div>

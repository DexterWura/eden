<?php
$hubType = $hubType ?? 'category';
$hubName = $hubName ?? '';
$hubIcon = $hubIcon ?? 'fa-solid fa-layer-group';
$introduction = trim((string) ($introduction ?? ''));
$marketContext = trim((string) ($marketContext ?? ''));
$faqs = $faqs ?? [];
$startups = $startups ?? collect();
$relatedCategories = $relatedCategories ?? collect();
$categoryCounts = $categoryCounts ?? collect();
?>
<section class="page-head discovery-hub-head">
  <div class="wrap">
    <nav class="hub-breadcrumb" aria-label="Breadcrumb">
      <a href="<?= e(url('/')) ?>">Apps</a>
      <span>/</span>
      <a href="<?= e($hubType === 'category' ? url('/categories') : url('/')) ?>"><?= e($hubType === 'category' ? 'Categories' : 'Locations') ?></a>
    </nav>
    <div class="hub-title-row">
      <span class="hub-icon"><i class="<?= e($hubIcon ?: 'fa-solid fa-layer-group') ?>" aria-hidden="true"></i></span>
      <div>
        <span class="sidebar-eyebrow"><?= e($hubType === 'category' ? 'App category' : 'App ecosystem') ?></span>
        <h1><?= e($hubType === 'category' ? $hubName . ' startups' : 'Startups in ' . $hubName) ?></h1>
        <p><?= number_format((int) $startups->total()) ?> active <?= $startups->total() === 1 ? 'listing' : 'listings' ?></p>
      </div>
    </div>
  </div>
</section>

<div class="wrap hub-layout">
  <main class="hub-main">
    <?php if ($introduction !== ''): ?>
    <section class="hub-editorial">
      <h2>About this ecosystem</h2>
      <p><?= nl2br(e($introduction)) ?></p>
      <?php if ($marketContext !== ''): ?>
      <h3>Market context</h3>
      <p><?= nl2br(e($marketContext)) ?></p>
      <?php endif; ?>
    </section>
    <?php endif; ?>

    <section aria-labelledby="hub-startups-heading">
      <header class="section-header">
        <div>
          <h2 id="hub-startups-heading" class="section-heading">Products to explore</h2>
          <p class="section-sub">Ranked by community activity, with featured products first.</p>
        </div>
      </header>
      <div class="startup-list hub-startup-list">
        <?php foreach ($startups as $startup):
          $cardVariant = 'feed';
          $showRank = false;
          include __DIR__ . '/_startup-card.php';
        endforeach; ?>
      </div>
      <?php if ($startups->isEmpty()): ?>
      <div class="hub-empty">
        <h3>No active products yet</h3>
        <p>Know an app that belongs here?</p>
        <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit it to Eden</a>
      </div>
      <?php endif; ?>
      <?php if ($startups->hasPages()): ?>
      <div class="pagination-container"><?= $startups->links() ?></div>
      <?php endif; ?>
    </section>

    <?php if ($faqs !== []): ?>
    <section class="hub-faq" aria-labelledby="hub-faq-heading">
      <h2 id="hub-faq-heading">Frequently asked questions</h2>
      <?php foreach ($faqs as $item): ?>
      <details>
        <summary><?= e($item['question']) ?></summary>
        <p><?= nl2br(e($item['answer'])) ?></p>
      </details>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>
  </main>

  <aside class="hub-sidebar" aria-label="Related discovery links">
    <?php if ($categoryCounts->isNotEmpty()): ?>
    <section class="sidebar-panel">
      <div class="sidebar-panel-head"><h2>Popular sectors</h2></div>
      <div class="hub-sector-list">
        <?php foreach ($categoryCounts as $row): ?>
        <a href="<?= e(url('/categories/' . \Illuminate\Support\Str::slug($row->category))) ?>">
          <span><?= e($row->category) ?></span><strong><?= (int)$row->total ?></strong>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
    <?php if ($relatedCategories->isNotEmpty()): ?>
    <section class="sidebar-panel">
      <div class="sidebar-panel-head"><h2>Related categories</h2></div>
      <div class="hub-sector-list">
        <?php foreach ($relatedCategories as $related): ?>
        <a href="<?= e(url('/categories/' . $related->slug)) ?>">
          <span><?= e($related->name) ?></span><strong><?= (int)$related->startups_count ?></strong>
        </a>
        <?php endforeach; ?>
      </div>
    </section>
    <?php endif; ?>
    <section class="sidebar-panel sidebar-newsletter">
      <span class="sidebar-eyebrow">Build the directory</span>
      <h2>Launching something?</h2>
      <p>Add a detailed profile and reach customers, founders and investors.</p>
      <a href="<?= e(url('/submit')) ?>" class="btn btn-primary btn-block">Submit your app</a>
    </section>
  </aside>
</div>

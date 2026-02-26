<section class="page-head">
  <div class="wrap">
    <h1>Categories</h1>
    <p>Browse startups by category.</p>
  </div>
</section>

<div class="wrap">
  <h2 class="section-title">All categories</h2>
  <div class="category-list">
    <?php
    $categories = $categories ?? collect();
    foreach ($categories as $cat):
      $count = (int) ($cat->count ?? 0);
      $label = $count === 1 ? '1 startup' : $count . ' startups';
      $name = $cat->name ?? $cat->category ?? '';
    ?>
    <a href="<?= e(url('/?category=' . urlencode($name))) ?>" class="category-card"><strong><?= e($name) ?></strong><span><?= e($label) ?></span></a>
    <?php endforeach; ?>
    <?php if ($categories->isEmpty()): ?>
    <p class="text-muted">No categories yet. <a href="<?= e(url('/submit')) ?>">Submit your startup</a> to create the first.</p>
    <?php endif; ?>
  </div>

  <div class="cta-strip">
    <h3>Don't see your category?</h3>
    <p>Submit your startup and we'll list it under the best fit. You can suggest new categories when you submit.</p>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
  </div>
</div>

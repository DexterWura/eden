<section class="page-head">
  <div class="wrap">
    <h1>Categories</h1>
    <p>Browse apps by category.</p>
  </div>
</section>

<div class="wrap">
  <h2 class="section-title">All categories</h2>
  <div class="category-list">
    <?php
    $categories = $categories ?? collect();
    foreach ($categories as $cat):
      $count = (int) ($cat->count ?? 0);
      $label = $count === 1 ? '1 app' : $count . ' apps';
      $name = $cat->name ?? $cat->category ?? '';
    ?>
    <a href="<?= e(url('/categories/' . ($cat->slug ?? \Illuminate\Support\Str::slug($name)))) ?>" class="category-card">
      <span class="category-card-icon"><i class="<?= e($cat->icon ?: 'fa-solid fa-layer-group') ?>" aria-hidden="true"></i></span>
      <strong><?= e($name) ?></strong>
      <span><?= e($label) ?></span>
      <?php if (!empty($cat->introduction)): ?><small><?= e(\Illuminate\Support\Str::limit($cat->introduction, 100)) ?></small><?php endif; ?>
    </a>
    <?php endforeach; ?>
    <?php if ($categories->isEmpty()): ?>
    <p class="text-muted">No categories yet. <a href="<?= e(url('/submit')) ?>">Submit your app</a> to create the first.</p>
    <?php endif; ?>
  </div>

  <div class="cta-strip">
    <h3>Don't see your category?</h3>
    <p>Submit your app and we'll list it under the best fit. You can suggest new categories when you submit.</p>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your app</a>
  </div>
</div>

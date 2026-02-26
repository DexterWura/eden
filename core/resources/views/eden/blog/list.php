<?php
$posts = $posts ?? collect();
?>
<section class="page-head">
  <div class="wrap">
    <h1>Blog</h1>
    <p>Articles and updates.</p>
  </div>
</section>

<div class="wrap content-block">
  <?php if ($posts->isEmpty()): ?>
  <p class="section-empty">No posts yet. Check back later.</p>
  <?php else: ?>
  <div class="blog-list" style="display: grid; gap: 24px;">
    <?php foreach ($posts as $post): ?>
    <article class="blog-list-item" style="border: 1px solid var(--border, #e2e8f0); border-radius: 8px; padding: 20px;">
      <h2 style="margin-top: 0; font-size: 1.25rem;">
        <a href="<?= e(url('/blog/' . $post->slug)) ?>"><?= e($post->title) ?></a>
      </h2>
      <?php if ($post->excerpt): ?>
      <p style="color: var(--text-muted, #64748b); margin: 8px 0 12px; font-size: 0.95rem;"><?= e($post->excerpt) ?></p>
      <?php endif; ?>
      <p style="font-size: 0.875rem; color: var(--text-muted, #64748b);">
        <?= $post->published_at ? $post->published_at->format('F j, Y') : '' ?>
      </p>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if ($posts->hasPages()): ?>
  <div style="margin-top: 24px;"><?= $posts->links() ?></div>
  <?php endif; ?>
  <?php endif; ?>
</div>

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
    <article class="blog-list-item" style="border: 1px solid var(--border, #e2e8f0); border-radius: 8px; overflow: hidden; display: grid; grid-template-rows: auto 1fr; background: #fff;">
      <a href="<?= e(url('/blog/' . $post->slug)) ?>" style="display: block;">
        <?php if ($post->og_image_url): ?>
        <img src="<?= e($post->og_image_url) ?>" alt="<?= e($post->title) ?>" style="width: 100%; height: 200px; object-fit: cover; display: block;">
        <?php else: ?>
        <div style="width: 100%; height: 200px; background: linear-gradient(135deg, #e2e8f0, #cbd5f5); display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 0.9rem;">
          Blog image
        </div>
        <?php endif; ?>
      </a>
      <div style="padding: 16px 20px 18px;">
        <h2 style="margin-top: 0; margin-bottom: 8px; font-size: 1.25rem;">
          <a href="<?= e(url('/blog/' . $post->slug)) ?>"><?= e($post->title) ?></a>
        </h2>
        <?php if ($post->excerpt): ?>
        <p style="color: var(--text-muted, #64748b); margin: 0 0 10px; font-size: 0.95rem;"><?= e($post->excerpt) ?></p>
        <?php endif; ?>
        <p style="font-size: 0.85rem; color: var(--text-muted, #64748b); margin: 0;">
          <span>by <?= e(optional($post->author)->name ?? 'Admin') ?></span>
          <?php if ($post->published_at): ?>
          <span style="margin-left: 8px;">&middot;</span>
          <span><?= $post->published_at->format('F j, Y') ?></span>
          <?php endif; ?>
        </p>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if ($posts->hasPages()): ?>
  <div style="margin-top: 24px;"><?= $posts->links() ?></div>
  <?php endif; ?>
  <?php endif; ?>
</div>

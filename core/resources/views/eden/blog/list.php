<?php
$posts = $posts ?? collect();
$blogAd = $blogAd ?? null;
?>
<section class="page-head">
  <div class="wrap">
    <h1>Blog</h1>
    <p>Articles and updates.</p>
  </div>
</section>

<div class="wrap content-block">
  <?php if ($blogAd): ?>
  <?php
    $blogAdPath = $blogAd->image_path ?? '';
    $blogAdIsExternal = is_string($blogAdPath) && ($blogAdPath !== '') && (str_starts_with($blogAdPath, 'http://') || str_starts_with($blogAdPath, 'https://'));
    $blogAdSrc = $blogAdIsExternal ? $blogAdPath : asset($blogAdPath);
  ?>
  <div class="blog-ad-spot">
    <a href="<?= e($blogAd->target_url) ?>" target="_blank" rel="noopener noreferrer">
      <img src="<?= e($blogAdSrc) ?>" alt="Sponsored ad">
    </a>
  </div>
  <?php endif; ?>

  <?php if ($posts->isEmpty()): ?>
  <p class="section-empty">No posts yet. Check back later.</p>
  <?php else: ?>
  <div class="blog-list">
    <?php foreach ($posts as $post): ?>
    <article class="blog-list-item">
      <a href="<?= e(url('/blog/' . $post->slug)) ?>" class="blog-list-item-image">
        <?php if ($post->og_image_url): ?>
        <img src="<?= e($post->og_image_url) ?>" alt="<?= e($post->title) ?>">
        <?php else: ?>
        <div class="blog-list-item-placeholder">Blog image</div>
        <?php endif; ?>
      </a>
      <div class="blog-list-item-body">
        <h2 class="blog-list-item-title">
          <a href="<?= e(url('/blog/' . $post->slug)) ?>"><?= e($post->title) ?></a>
        </h2>
        <?php if ($post->excerpt): ?>
        <p class="blog-list-item-excerpt"><?= e($post->excerpt) ?></p>
        <?php endif; ?>
        <p class="blog-list-item-meta">
          <span>by <?= e($post->author_name) ?></span>
          <?php if ($post->published_at): ?>
          <span>&middot;</span>
          <span><?= $post->published_at->format('F j, Y') ?></span>
          <?php endif; ?>
        </p>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
  <?php if ($posts->hasPages()): ?>
  <div class="blog-pagination"><?= $posts->links() ?></div>
  <?php endif; ?>
  <?php endif; ?>
</div>

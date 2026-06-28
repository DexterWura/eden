<?php
$post = $post ?? null;
if (!$post) return;
?>
<section class="page-head blog-page-head">
  <div class="wrap">
    <a href="<?= e(url('/blog')) ?>" class="back-link">&larr; Back to blog</a>
    <h1><?= e($post->title) ?></h1>
    <?php if ($post->published_at): ?>
    <p class="blog-meta"><?= $post->published_at->format('F j, Y') ?></p>
    <?php endif; ?>
  </div>
</section>

<div class="wrap content-block">
  <article class="blog-post">
    <?php if ($post->og_image_url): ?>
    <figure class="blog-featured-image">
      <img src="<?= e($post->og_image_url) ?>" alt="<?= e($post->title) ?>">
    </figure>
    <?php endif; ?>

    <?php if ($post->excerpt): ?>
    <p class="blog-excerpt"><?= e($post->excerpt) ?></p>
    <?php endif; ?>

    <div class="blog-body">
      <?= $post->body ?>
    </div>
  </article>
</div>

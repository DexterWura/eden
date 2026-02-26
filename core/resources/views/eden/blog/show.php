<?php
$post = $post ?? null;
if (!$post) return;
?>
<section class="page-head">
  <div class="wrap">
    <h1><?= e($post->title) ?></h1>
    <?php if ($post->published_at): ?>
    <p style="font-size: 0.95rem; color: var(--text-muted, #64748b);"><?= $post->published_at->format('F j, Y') ?></p>
    <?php endif; ?>
  </div>
</section>

<div class="wrap content-block">
  <article class="blog-post">
    <?php if ($post->excerpt): ?>
    <p class="blog-excerpt" style="font-size: 1.1rem; color: var(--text-muted, #64748b); margin-bottom: 24px;"><?= e($post->excerpt) ?></p>
    <?php endif; ?>
    <div class="blog-body">
      <?= $post->body ?>
    </div>
  </article>
  <p style="margin-top: 32px;"><a href="<?= e(url('/blog')) ?>">&larr; Back to blog</a></p>
</div>

<style>
.blog-body { line-height: 1.7; }
.blog-body p { margin-bottom: 1em; }
.blog-body h2 { font-size: 1.35rem; margin-top: 1.5em; margin-bottom: 0.5em; }
.blog-body h3 { font-size: 1.15rem; margin-top: 1.25em; margin-bottom: 0.4em; }
.blog-body ul, .blog-body ol { margin: 0.75em 0; padding-left: 1.5em; }
.blog-body a { color: var(--primary, #2563eb); text-decoration: underline; }
.blog-body img { max-width: 100%; height: auto; border-radius: 6px; }
</style>

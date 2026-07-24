<?php
$post = $post ?? null;
if (!$post) return;
?>
<section class="page-head blog-page-head">
  <div class="wrap">
    <a href="<?= e(url('/blog')) ?>" class="back-link">&larr; Back to blog</a>
    <h1><?= e($post->title) ?></h1>
    <?php if ($post->published_at): ?>
    <p class="blog-meta">
      By <?= e($post->author_name) ?> · Published <?= $post->published_at->format('F j, Y') ?>
      <?php if ($post->updated_at && !$post->updated_at->equalTo($post->created_at)): ?> · Updated <?= $post->updated_at->format('F j, Y') ?><?php endif; ?>
      <?php if ($post->editorial_reviewed_at): ?> · Reviewed by Eden<?php endif; ?>
    </p>
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

    <?php if (!empty($post->source_urls)): ?>
    <section class="blog-sources" aria-labelledby="article-sources-heading">
      <h2 id="article-sources-heading">Sources and further reading</h2>
      <ol>
        <?php foreach ($post->source_urls as $sourceUrl): ?>
        <li><a href="<?= e($sourceUrl) ?>" target="_blank" rel="noopener noreferrer"><?= e(parse_url($sourceUrl, PHP_URL_HOST) ?: $sourceUrl) ?></a></li>
        <?php endforeach; ?>
      </ol>
    </section>
    <?php endif; ?>
  </article>

  <?php $relatedStartups = $relatedStartups ?? collect(); ?>
  <?php if ($relatedStartups->isNotEmpty()): ?>
  <section class="blog-related" aria-labelledby="related-startups-heading">
    <h2 id="related-startups-heading">Startups mentioned by this topic</h2>
    <div class="startup-list hub-startup-list">
      <?php foreach ($relatedStartups as $startup):
        $cardVariant = 'feed';
        $showRank = false;
        include dirname(__DIR__) . '/_startup-card.php';
      endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php $relatedPosts = $relatedPosts ?? collect(); ?>
  <?php if ($relatedPosts->isNotEmpty()): ?>
  <section class="blog-related" aria-labelledby="continue-reading-heading">
    <h2 id="continue-reading-heading">Continue reading</h2>
    <div class="blog-related-grid">
      <?php foreach ($relatedPosts as $relatedPost): ?>
      <a href="<?= e(url('/blog/' . $relatedPost->slug)) ?>">
        <span><?= e($relatedPost->published_at?->format('M j, Y')) ?></span>
        <strong><?= e($relatedPost->title) ?></strong>
        <p><?= e(\Illuminate\Support\Str::limit($relatedPost->excerpt, 120)) ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<?php
$about = $about ?? [];
$headTitle = $about['head_title'] ?? 'About';
$headSubtitle = $about['head_subtitle'] ?? '';
$whatWeDoTitle = $about['what_we_do_title'] ?? 'What we do';
$whatWeDoBody = $about['what_we_do_body'] ?? '';
$forFoundersTitle = $about['for_founders_title'] ?? 'For founders';
$forFoundersBody = $about['for_founders_body'] ?? '';
$forVisitorsTitle = $about['for_visitors_title'] ?? 'For visitors';
$forVisitorsBody = $about['for_visitors_body'] ?? '';
$guidelinesTitle = $about['guidelines_title'] ?? 'Guidelines';
$guidelinesItems = $about['guidelines_items'] ?? [];
$ctaTitle = $about['cta_title'] ?? 'Ready to list your startup?';
$ctaSubtitle = $about['cta_subtitle'] ?? 'Submit in under 2 minutes.';
?>
<section class="page-head">
  <div class="wrap">
    <h1><?= e($headTitle) ?></h1>
    <?php if ($headSubtitle !== ''): ?><p><?= e($headSubtitle) ?></p><?php endif; ?>
  </div>
</section>

<div class="wrap content-block">
  <?php if ($whatWeDoTitle !== '' || $whatWeDoBody !== ''): ?>
  <h2><?= e($whatWeDoTitle) ?></h2>
  <?php if ($whatWeDoBody !== ''): ?><p><?= nl2br(e($whatWeDoBody)) ?></p><?php endif; ?>
  <?php endif; ?>

  <?php if ($forFoundersTitle !== '' || $forFoundersBody !== ''): ?>
  <h2><?= e($forFoundersTitle) ?></h2>
  <?php if ($forFoundersBody !== ''): ?><p><?= nl2br(e($forFoundersBody)) ?></p><?php endif; ?>
  <?php endif; ?>

  <?php if ($forVisitorsTitle !== '' || $forVisitorsBody !== ''): ?>
  <h2><?= e($forVisitorsTitle) ?></h2>
  <?php if ($forVisitorsBody !== ''): ?><p><?= nl2br(e($forVisitorsBody)) ?></p><?php endif; ?>
  <?php endif; ?>

  <?php if ($guidelinesTitle !== '' || !empty($guidelinesItems)): ?>
  <h2><?= e($guidelinesTitle) ?></h2>
  <?php if (!empty($guidelinesItems)): ?>
  <ul>
    <?php foreach ($guidelinesItems as $item): ?>
    <li><?= e($item) ?></li>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
  <?php endif; ?>

  <div class="cta-strip">
    <h3><?= e($ctaTitle) ?></h3>
    <?php if ($ctaSubtitle !== ''): ?><p><?= e($ctaSubtitle) ?></p><?php endif; ?>
    <a href="<?= e(url('/submit')) ?>" class="btn btn-primary">Submit your startup</a>
    <a href="<?= e(url('/contact')) ?>" class="btn btn-ghost">Contact us</a>
  </div>
</div>

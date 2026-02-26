<?php
$startup = $startup ?? null;
if (!$startup) return;
?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(url('/startup/' . $startup->slug)) ?>" class="back-link">&larr; Back to <?= e($startup->name) ?></a>
    <h1>Claim this startup</h1>
    <p>You are about to claim <strong><?= e($startup->name) ?></strong> as your own. Only proceed if you own or represent this startup or product.</p>
  </div>
</section>
<div class="wrap content-block form-max">
  <p class="claim-prompt">Do you truly own this startup or product?</p>
  <div class="claim-actions">
    <form action="<?= e(route('startup.claim.confirm', $startup->slug)) ?>" method="POST" style="display: inline;">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
      <input type="hidden" name="confirm" value="yes">
      <button type="submit" class="btn btn-primary">Yes, I own it</button>
    </form>
    <a href="<?= e(url('/startup/' . $startup->slug)) ?>" class="btn btn-ghost">Cancel</a>
  </div>
</div>

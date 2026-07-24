<?php
$startup = $startup ?? null;
if (!$startup) return;
$hasWebsite = !empty($startup->website);
?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(route('startup.claim', $startup->slug)) ?>" class="back-link">&larr; Back</a>
    <h1>Prove ownership</h1>
    <p>Choose how you want to verify that you control <strong><?= e($startup->name) ?></strong>.</p>
  </div>
</section>
<div class="wrap content-block form-max">
  <?php if (!$hasWebsite): ?>
  <p class="form-hint" style="margin-bottom: 16px; color: var(--text-muted, #64748b);">This app has no website URL. Please ask the site admin to add a website for this app before you can prove ownership.</p>
  <?php endif; ?>
  <form action="<?= e(route('startup.claim.start', $startup->slug)) ?>" method="POST">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-group">
      <label class="form-label">Verification method</label>
      <div class="claim-method-options">
        <label class="claim-method-card">
          <input type="radio" name="method" value="dns" <?= $hasWebsite ? '' : 'disabled' ?> required>
          <span class="claim-method-title"><i class="fa-solid fa-globe" aria-hidden="true"></i> Domain DNS</span>
          <span class="claim-method-desc">Add a DNS TXT record to your domain. We’ll check it to confirm you control the domain.</span>
        </label>
        <label class="claim-method-card">
          <input type="radio" name="method" value="file" <?= $hasWebsite ? '' : 'disabled' ?>>
          <span class="claim-method-title"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> TXT file</span>
          <span class="claim-method-desc">Place a verification file in the root of your website. We’ll fetch it to confirm ownership.</span>
        </label>
      </div>
      <?php if (isset($errors) && $errors->has('method')): ?>
      <p class="form-hint" style="color: var(--error, #c00); margin-top: 8px;"><?= e($errors->first('method')) ?></p>
      <?php endif; ?>
    </div>
    <div class="form-actions" style="margin-top: 24px;">
      <?php if ($hasWebsite): ?>
      <button type="submit" class="btn btn-primary">Continue</button>
      <?php endif; ?>
      <a href="<?= e(route('startup.claim', $startup->slug)) ?>" class="btn btn-ghost">Back</a>
    </div>
  </form>
</div>

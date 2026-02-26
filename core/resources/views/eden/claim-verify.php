<?php
$startup = $startup ?? null;
$pending = $pending ?? null;
if (!$startup || !$pending) return;
$isDns = $pending->method === 'dns';
$isFile = $pending->method === 'file';
$dnsRecord = $dnsRecord ?? [];
$domain = $domain ?? null;
$fileUrl = $fileUrl ?? null;
?>
<section class="page-head">
  <div class="wrap">
    <a href="<?= e(route('startup.claim', ['slug' => $startup->slug, 'step' => 'method'])) ?>" class="back-link">&larr; Back</a>
    <h1>Verify ownership</h1>
    <p>Complete the steps below, then click <strong>Verify</strong>.</p>
  </div>
</section>
<div class="wrap content-block form-max">
  <?php if ($isDns && $domain): ?>
  <div class="claim-verify-block">
    <h2 class="claim-verify-heading"><i class="fa-solid fa-globe" aria-hidden="true"></i> DNS verification</h2>
    <p>Add the following TXT record to your domain <strong><?= e($domain) ?></strong> (via your DNS provider or host).</p>
    <div class="claim-dns-record">
      <div class="claim-dns-row">
        <span class="claim-dns-label">Type</span>
        <span class="claim-dns-value">TXT</span>
      </div>
      <div class="claim-dns-row">
        <span class="claim-dns-label">Name / Host</span>
        <span class="claim-dns-value">@ or _eden-verification</span>
      </div>
      <div class="claim-dns-row">
        <span class="claim-dns-label">Value / Content</span>
        <span class="claim-dns-value claim-dns-value-code"><?= e($dnsRecord['full_record'] ?? $dnsRecord['value'] ?? '') ?></span>
      </div>
    </div>
    <p class="form-hint">DNS changes can take a few minutes to propagate. If verification fails, wait a bit and try again.</p>
  </div>
  <?php endif; ?>

  <?php if ($isFile && $pending->verification_file_name): ?>
  <div class="claim-verify-block">
    <h2 class="claim-verify-heading"><i class="fa-solid fa-file-lines" aria-hidden="true"></i> File verification</h2>
    <p>Create a file named <strong><?= e($pending->verification_file_name) ?></strong> with exactly this content (no extra spaces or lines):</p>
    <pre class="claim-file-content"><?= e($pending->verification_code) ?></pre>
    <p>Place the file in the <strong>root</strong> of your website so it is available at:</p>
    <p><a href="<?= e($fileUrl) ?>" target="_blank" rel="noopener" class="claim-file-url"><?= e($fileUrl) ?></a></p>
    <p class="form-hint">After uploading, click Verify below. We’ll fetch this URL and check the content.</p>
  </div>
  <?php endif; ?>

  <form action="<?= e(route('startup.claim.verify', $startup->slug)) ?>" method="POST" class="claim-verify-form">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Verify ownership</button>
      <a href="<?= e(route('startup.claim', ['slug' => $startup->slug, 'step' => 'method'])) ?>" class="btn btn-ghost">Choose another method</a>
    </div>
  </form>
</div>

<?php
/** @var string $segment */
/** @var array $meta */
/** @var \Illuminate\Support\Collection|\App\Models\PaymentGateway[] $gateways */
$price = $meta['price'];
$currency = $meta['currency'];
$width = $meta['width'];
$height = $meta['height'];
$label = $meta['label'];
$description = $meta['description'];
$formAction = url('/advertise/' . $segment);
?>
<section class="page-head">
  <div class="wrap">
    <h1><?= e($label) ?></h1>
    <p><?= e($description) ?></p>
  </div>
</section>

<div class="wrap content-block">
  <div class="card" style="max-width: 640px; margin: 0 auto;">
    <h2 style="margin-top: 0;"><?= e($label) ?> – <?= e($currency) ?><?= number_format($price, 2) ?>/month</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
      Upload a <?= (int) $width ?>×<?= (int) $height ?> banner. No account required – pay once and your ad runs for 30 days.
    </p>

    <form action="<?= e($formAction) ?>" method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">

      <div class="form-group">
        <label class="form-label" for="contactEmail">Contact email</label>
        <input type="email" id="contactEmail" name="contact_email" class="form-input" required
               value="<?= e(old('contact_email')) ?>" placeholder="you@example.com">
      </div>

      <div class="form-group">
        <label class="form-label" for="targetUrl">Ad click URL</label>
        <input type="url" id="targetUrl" name="target_url" class="form-input" required
               value="<?= e(old('target_url')) ?>" placeholder="https://your-site.com">
        <p class="form-help">Where visitors go when they click your banner.</p>
      </div>

      <div class="form-group">
        <label class="form-label" for="adImage">Banner image (<?= (int) $width ?>×<?= (int) $height ?>)</label>
        <input type="file" id="adImage" name="image" class="form-input" accept="image/*" required>
        <p class="form-help">Exact size required – uploads that are not <?= (int) $width ?>×<?= (int) $height ?> pixels will be rejected.</p>
      </div>

      <div class="form-group">
        <label class="form-label" for="gateway">Payment method</label>
        <select id="gateway" name="gateway" class="form-input" required>
          <option value="">Select a gateway</option>
          <?php foreach ($gateways as $gateway): ?>
          <option value="<?= e($gateway->alias) ?>" <?= old('gateway') === $gateway->alias ? 'selected' : '' ?>>
            <?= e($gateway->name) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-actions" style="margin-top: 20px;">
        <button type="submit" class="btn btn-primary btn-block">
          Buy ad – <?= e($currency) ?><?= number_format($price, 2) ?>
        </button>
      </div>
    </form>
    <p style="margin-top: 20px; font-size: 0.875rem;">
      <a href="<?= e(url('/advertise')) ?>">← All ad spots</a>
    </p>
  </div>
</div>

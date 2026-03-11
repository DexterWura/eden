<?php
/** @var float $price */
/** @var string $currency */
/** @var \Illuminate\Support\Collection|\App\Models\PaymentGateway[] $gateways */
/** @var int $width */
/** @var int $height */
?>
<section class="page-head">
  <div class="wrap">
    <h1>Advertise on the blog</h1>
    <p>Place your banner on the blog page for one month.</p>
  </div>
</section>

<div class="wrap content-block">
  <div class="card" style="max-width: 640px; margin: 0 auto;">
    <h2 style="margin-top: 0;">Blog ad spot – <?= e($currency) ?><?= number_format($price, 2) ?>/month</h2>
    <p style="color: var(--text-muted); font-size: 0.95rem;">
      Upload a <?= (int) $width ?>x<?= (int) $height ?> banner. No account required – just pay and your ad will run for 30 days on the blog page.
    </p>

    <form action="<?= e(url('/advertise/blog')) ?>" method="POST" enctype="multipart/form-data" style="margin-top: 16px;">
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
        <label class="form-label" for="adImage">Banner image (<?= (int) $width ?>x<?= (int) $height ?>)</label>
        <input type="file" id="adImage" name="image" class="form-input" accept="image/*" required>
        <p class="form-help">Exact size required – uploads that are not <?= (int) $width ?>x<?= (int) $height ?> pixels will be rejected.</p>
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
          Buy blog ad – <?= e($currency) ?><?= number_format($price, 2) ?>
        </button>
      </div>
    </form>
  </div>
</div>


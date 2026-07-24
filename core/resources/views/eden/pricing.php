<?php
$isPro = $isPro ?? false;
$gateways = $gateways ?? [];
?>
<section class="page-head">
  <div class="wrap">
    <h1>Go Pro</h1>
    <p>One payment. Lifetime access. Unlock everything.</p>
  </div>
</section>

<div class="wrap content-block pricing-page">

  <?php if (session('success')): ?>
  <div class="pricing-alert pricing-alert--success">
    <i class="fa-solid fa-circle-check"></i> <?= e(session('success')) ?>
  </div>
  <?php endif; ?>
  <?php if (session('error')): ?>
  <div class="pricing-alert pricing-alert--error">
    <i class="fa-solid fa-circle-xmark"></i> <?= e(session('error')) ?>
  </div>
  <?php endif; ?>

  <?php if ($isPro): ?>
  <div class="pricing-already-pro">
    <div class="pricing-already-pro-icon"><i class="fa-solid fa-crown"></i></div>
    <h2>You're a Pro member!</h2>
    <p>You already have lifetime access to all Pro features. Thank you for your support.</p>
    <a href="<?= e(url('/founder')) ?>" class="btn btn-primary">Go to dashboard</a>
  </div>
  <?php else: ?>

  <div class="pricing-grid">
    <div class="pricing-card pricing-card--free">
      <div class="pricing-card-header">
        <span class="pricing-badge">Free</span>
        <h2 class="pricing-price">$0</h2>
        <p class="pricing-period">forever</p>
      </div>
      <ul class="pricing-features">
        <li><i class="fa-solid fa-check"></i> 1 app</li>
        <li><i class="fa-solid fa-check"></i> Submit &amp; appear in search &amp; categories</li>
        <li><i class="fa-solid fa-check"></i> Upvote other apps</li>
        <li><i class="fa-solid fa-check"></i> Basic founder profile</li>
        
        <li class="pricing-feature--disabled"><i class="fa-solid fa-xmark"></i> Unlimited apps</li>
        <li class="pricing-feature--disabled"><i class="fa-solid fa-xmark"></i> Raise funds for your app</li>
        <li class="pricing-feature--disabled"><i class="fa-solid fa-xmark"></i> Featured placement</li>
        <li class="pricing-feature--disabled"><i class="fa-solid fa-xmark"></i> Embed listing badge</li>
        <li class="pricing-feature--disabled"><i class="fa-solid fa-xmark"></i> Publish blog posts</li>
        <li class="pricing-feature--disabled"><i class="fa-solid fa-xmark"></i> Analytics dashboard</li>
        <li class="pricing-feature--disabled"><i class="fa-solid fa-xmark"></i> Priority support</li>
      </ul>
      <div class="pricing-card-footer">
        <a href="<?= e(url('/submit')) ?>" class="btn btn-ghost btn-block">Get started free</a>
      </div>
    </div>

    <div class="pricing-card pricing-card--pro">
      <div class="pricing-popular-tag">Most popular</div>
      <div class="pricing-card-header">
        <span class="pricing-badge pricing-badge--pro"><i class="fa-solid fa-crown"></i> Pro</span>
        <h2 class="pricing-price">$9.99</h2>
        <p class="pricing-period">one-time · lifetime access</p>
      </div>
      <ul class="pricing-features">
        <li><i class="fa-solid fa-check"></i> Everything in Free</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-layer-group"></i> Unlimited apps</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-hand-holding-dollar"></i> Raise funds for your app</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-star"></i> Feature your app on homepage</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-arrow-up"></i> Priority placement in the app feed</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-pen-nib"></i> Publish blog posts</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-chart-line"></i> Analytics dashboard (views, clicks)</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-headset"></i> Priority support</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-bolt"></i> Early access to new features</li>
        <li class="pricing-feature--highlight"><i class="fa-solid fa-link"></i> Dofollow website backlink</li>
      </ul>
      <div class="pricing-card-footer">
        <?php if (auth()->check()): ?>
          <?php if (count($gateways) > 0): ?>
          <form action="<?= e(url('/checkout')) ?>" method="POST" class="pricing-checkout-form">
            <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
            <div class="pricing-gateway-select">
              <label class="form-label">Pay with</label>
              <div class="pricing-gateway-options">
                <?php $firstGw = true; foreach ($gateways as $gw): ?>
                <label class="pricing-gateway-option">
                  <input type="radio" name="gateway" value="<?= e($gw->alias) ?>" <?= $firstGw ? 'checked' : '' ?> required>
                  <span class="pricing-gateway-label">
                    <?php if ($gw->alias === 'paypal'): ?>
                      <i class="fa-brands fa-paypal"></i> PayPal
                    <?php elseif ($gw->alias === 'paynow'): ?>
                      <i class="fa-solid fa-building-columns"></i> Paynow
                    <?php else: ?>
                      <i class="fa-solid fa-credit-card"></i> <?= e($gw->name) ?>
                    <?php endif; ?>
                  </span>
                </label>
                <?php $firstGw = false; endforeach; ?>
              </div>
            </div>
            <button type="submit" class="btn btn-primary btn-block btn-lg">Upgrade for $9.99</button>
          </form>
          <?php else: ?>
          <p class="pricing-no-gateways">Payment gateways are being configured. Check back soon.</p>
          <?php endif; ?>
        <?php else: ?>
        <a href="<?= e(url('/login?redirect=' . urlencode(url('/pricing')))) ?>" class="btn btn-primary btn-block btn-lg">Log in to upgrade</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="pricing-faq">
    <h2>Frequently asked questions</h2>
    <div class="pricing-faq-grid">
      <div class="pricing-faq-item">
        <h3>Is it really a one-time payment?</h3>
        <p>Yes. Pay once, get Pro forever. No recurring charges, no hidden fees.</p>
      </div>
      <div class="pricing-faq-item">
        <h3>What does "featured" mean?</h3>
        <p>Your app appears in the Featured section on the homepage, giving you extra visibility to every visitor.</p>
      </div>
      <div class="pricing-faq-item">
        <h3>Can I write blog posts?</h3>
        <p>Pro members can publish blog posts linked to their app, sharing updates, launches, and milestones with the community.</p>
      </div>
      <div class="pricing-faq-item">
        <h3>What if I need a refund?</h3>
        <p>Contact us within 7 days of purchase and we'll issue a full refund, no questions asked.</p>
      </div>
    </div>
  </div>

  <?php endif; ?>
</div>

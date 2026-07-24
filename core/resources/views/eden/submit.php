<section class="page-head page-head--submit">
  <div class="wrap">
    <h1>Submit your startup</h1>
    <p>Get listed in under 2 minutes. Free for a basic listing.</p>
  </div>
</section>

<div class="wrap content-block submit-page">
  <div class="submit-form-wrap">
    <?php $submitterHasPro = auth()->check() && auth()->user()->isPro(); ?>
    <section class="submit-backlink-offer<?= $submitterHasPro ? ' submit-backlink-offer--active' : '' ?>" aria-labelledby="submit-backlink-offer-title">
      <div class="submit-backlink-offer__icon"><i class="fa-solid fa-link" aria-hidden="true"></i></div>
      <div class="submit-backlink-offer__copy">
        <h2 id="submit-backlink-offer-title"><?= $submitterHasPro ? 'Your Pro dofollow backlink is active' : 'Add a dofollow backlink with Pro' ?></h2>
        <p><?= $submitterHasPro
          ? 'Your startup website link will be dofollow while your account remains Pro.'
          : 'A basic listing is free and uses a nofollow website link. Pro is optional and unlocks a dofollow backlink.' ?></p>
      </div>
      <?php if (!$submitterHasPro): ?>
      <a href="<?= e(route('pricing')) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-primary"><i class="fa-solid fa-crown" aria-hidden="true"></i> View Pro</a>
      <?php endif; ?>
    </section>
    <form class="submit-form-card" action="<?= e(url('/submit')) ?>" method="POST" enctype="multipart/form-data">
      <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">

      <div class="submit-form-section">
        <h2 class="submit-form-section-title">Basics</h2>
        <div class="form-group">
          <label class="form-label" for="startup-name">Startup name</label>
          <input type="text" id="startup-name" name="name" class="form-input" placeholder="e.g. Nexus Pay" value="<?= e(old('name')) ?>" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="tagline">Short tagline</label>
          <input type="text" id="tagline" name="tagline" class="form-input" placeholder="One line that describes your startup" maxlength="255" value="<?= e(old('tagline')) ?>">
          <p class="form-hint">Max 255 characters. Shown on your card.</p>
        </div>
        <div class="form-group">
          <label class="form-label" for="description">Description</label>
          <textarea id="description" name="description" class="form-textarea" placeholder="Give visitors a useful overview of what the product does and how it works." rows="6" minlength="250" required><?= e(old('description')) ?></textarea>
          <p class="form-hint">At least 250 characters. Original, specific profiles are more likely to be discovered.</p>
        </div>
        <div class="form-group">
          <label class="form-label" for="category">Category</label>
          <select id="category" name="category" class="form-select" required>
            <option value="">Choose a category…</option>
            <?php foreach ($categories ?? [] as $cat): ?>
            <option value="<?= e($cat->name) ?>"<?= old('category') === $cat->name ? ' selected' : '' ?>><?= e($cat->name) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="submit-form-section">
        <h2 class="submit-form-section-title">Product story</h2>
        <p class="form-hint" style="margin-bottom: 16px;">Help customers and search visitors understand why your product matters.</p>
        <div class="form-group">
          <label class="form-label" for="problem_solved">What problem do you solve?</label>
          <textarea id="problem_solved" name="problem_solved" class="form-textarea" rows="4" minlength="80" maxlength="3000" required><?= e(old('problem_solved')) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label" for="target_customer">Who is it for?</label>
          <textarea id="target_customer" name="target_customer" class="form-textarea" rows="3" minlength="40" maxlength="1500" required><?= e(old('target_customer')) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label">Key features</label>
          <div class="form-row form-row--2">
            <?php $submittedFeatures = old('key_features', ['', '', '']); ?>
            <?php for ($featureIndex = 0; $featureIndex < 3; $featureIndex++): ?>
            <input type="text" name="key_features[]" class="form-input" minlength="5" maxlength="180" placeholder="Feature <?= $featureIndex + 1 ?>" value="<?= e($submittedFeatures[$featureIndex] ?? '') ?>" required>
            <?php endfor; ?>
          </div>
        </div>
        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="pricing_model">Pricing or business model</label>
            <input type="text" id="pricing_model" name="pricing_model" class="form-input" maxlength="120" placeholder="Free, subscription, transaction fee…" value="<?= e(old('pricing_model')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="markets_served">Markets served</label>
            <input type="text" id="markets_served" name="markets_served" class="form-input" maxlength="500" placeholder="Zimbabwe, Southern Africa, global…" value="<?= e(old('markets_served')) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="traction">Traction or proof <span class="submit-form-optional">optional</span></label>
          <textarea id="traction" name="traction" class="form-textarea" rows="3" maxlength="3000" placeholder="Customers, milestones, partnerships, revenue or usage."><?= e(old('traction')) ?></textarea>
        </div>
        <div class="form-group">
          <label class="form-label" for="founder_story">Founder story <span class="submit-form-optional">optional</span></label>
          <textarea id="founder_story" name="founder_story" class="form-textarea" rows="4" maxlength="5000" placeholder="Why did the team decide to build this?"><?= e(old('founder_story')) ?></textarea>
        </div>
      </div>

      <div class="submit-form-section">
        <h2 class="submit-form-section-title">Link &amp; location</h2>
        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="website">Website</label>
            <input type="url" id="website" name="website" class="form-input" placeholder="https://…" value="<?= e(old('website')) ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="location">Location</label>
            <input type="text" id="location" name="location" class="form-input" placeholder="e.g. Harare, Remote" value="<?= e(old('location')) ?>">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">Founders</label>
          <p class="form-hint" style="margin-bottom: 10px;">Add one or more founders. Use "Add another founder" to list co-founders.</p>
          <div id="founders-container">
            <?php
              $founderNames = old('founder_names', ['']);
              if (is_string($founderNames)) { $founderNames = [$founderNames]; }
              foreach ($founderNames as $i => $name):
            ?>
            <div class="founder-row" style="display: flex; gap: 10px; align-items: flex-start; margin-bottom: 10px;">
              <input type="text" name="founder_names[]" class="form-input" placeholder="e.g. Jane Doe" value="<?= e(is_string($name) ? $name : '') ?>" style="flex: 1;">
              <button type="button" class="btn btn-ghost founder-remove" aria-label="Remove founder" style="flex-shrink: 0; padding: 10px 14px;" title="Remove founder"><i class="fa-solid fa-trash-can"></i></button>
            </div>
            <?php endforeach; ?>
          </div>
          <button type="button" id="add-founder-btn" class="btn btn-ghost" style="margin-top: 6px; font-size: 0.875rem;"><i class="fa-solid fa-plus" aria-hidden="true"></i> Add another founder</button>
        </div>
      </div>

      <div class="submit-form-section">
        <h2 class="submit-form-section-title">Media <span class="submit-form-optional">optional</span></h2>
        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="logo">Startup logo</label>
            <p class="form-hint" style="margin-top: 0; margin-bottom: 8px;">80×80 px or smaller, square.</p>
            <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input form-input--file">
          </div>
          <div class="form-group">
            <label class="form-label">Product images</label>
            <p class="form-hint" style="margin-bottom: 8px;">You can add more than one image. Select multiple files at once or use "Add more images" to attach another set.</p>
            <div id="product-images-container">
              <input type="file" name="product_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple class="form-input form-input--file product-images-input">
            </div>
            <button type="button" id="add-more-product-images" class="btn btn-ghost" style="margin-top: 10px; font-size: 0.875rem;">
              <i class="fa-solid fa-plus" aria-hidden="true"></i> Add more images
            </button>
            <p id="product-images-summary" class="form-hint" style="margin-top: 8px; display: none;"></p>
          </div>
        </div>
      </div>

      <div class="submit-form-section">
        <h2 class="submit-form-section-title">Launch</h2>
        <div class="form-group">
          <label class="form-label" for="launch-date">Launching today?</label>
          <select id="launch-date" name="launch_today" class="form-select">
            <option value="">No</option>
            <option value="today"<?= old('launch_today') === 'today' ? ' selected' : '' ?>>Yes, we're launching today</option>
          </select>
          <p class="form-hint">If yes, your startup will appear on the Launching today page.</p>
        </div>
      </div>

      <?php if (!auth()->check()): ?>
      <div class="submit-form-section">
        <h2 class="submit-form-section-title">Your account</h2>
        <p class="form-hint" style="margin-bottom: 14px;">Create an account to manage your startup, or log in if you already have one.</p>

        <div style="display:flex;gap:0;margin-bottom:18px;border:1px solid var(--border,#e2e8f0);border-radius:8px;overflow:hidden">
          <button type="button" id="auth-tab-register" class="btn" style="flex:1;border:none;border-radius:0;padding:10px;font-size:0.9rem;font-weight:600;background:var(--accent,#00d4aa);color:#fff;cursor:pointer" onclick="toggleAuthTab('register')">
            <i class="fa-solid fa-user-plus"></i> Create account
          </button>
          <button type="button" id="auth-tab-login" class="btn" style="flex:1;border:none;border-radius:0;padding:10px;font-size:0.9rem;font-weight:500;background:var(--surface-hover,#f8fafc);color:var(--text-muted,#64748b);cursor:pointer" onclick="toggleAuthTab('login')">
            <i class="fa-solid fa-right-to-bracket"></i> I have an account
          </button>
        </div>
        <input type="hidden" name="auth_mode" id="auth-mode-input" value="<?= e(old('auth_mode', 'register')) ?>">

        <div id="auth-register-fields">
          <div class="form-group">
            <label class="form-label" for="auth-name">Full name</label>
            <input type="text" id="auth-name" name="auth_name" class="form-input" placeholder="Your full name" value="<?= e(old('auth_name')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="auth-email-register">Email address</label>
            <input type="email" id="auth-email-register" name="auth_email" class="form-input" placeholder="you@example.com" value="<?= e(old('auth_email')) ?>">
          </div>
          <div class="form-row form-row--2">
            <div class="form-group">
              <label class="form-label" for="auth-password">Password</label>
              <input type="password" id="auth-password" name="auth_password" class="form-input" placeholder="Min 8 characters">
            </div>
            <div class="form-group">
              <label class="form-label" for="auth-password-confirm">Confirm password</label>
              <input type="password" id="auth-password-confirm" name="auth_password_confirmation" class="form-input" placeholder="Repeat password">
            </div>
          </div>
        </div>

        <div id="auth-login-fields" style="display:none">
          <div class="form-group">
            <label class="form-label" for="auth-email-login">Email address</label>
            <input type="email" id="auth-email-login" name="login_email" class="form-input" placeholder="you@example.com" value="<?= e(old('login_email')) ?>">
          </div>
          <div class="form-group">
            <label class="form-label" for="auth-login-password">Password</label>
            <input type="password" id="auth-login-password" name="login_password" class="form-input" placeholder="Your password">
          </div>
        </div>
      </div>
      <?php endif; ?>

      <div style="background:var(--surface-hover,#f8fafc);border:1px solid var(--border,#e2e8f0);border-left:4px solid var(--accent,#00d4aa);border-radius:8px;padding:14px 18px;margin-bottom:16px;font-size:0.92rem;color:var(--text-muted,#64748b)">
        <i class="fa-solid fa-info-circle" style="color:var(--accent,#00d4aa);margin-right:6px"></i>
        Your startup will be reviewed by our team before going live. This usually takes less than 24 hours.
      </div>
      <div class="submit-form-actions">
        <button type="submit" class="btn btn-primary btn--submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit startup</button>
        <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script>
function toggleAuthTab(mode) {
  var regFields = document.getElementById('auth-register-fields');
  var loginFields = document.getElementById('auth-login-fields');
  var regTab = document.getElementById('auth-tab-register');
  var loginTab = document.getElementById('auth-tab-login');
  var modeInput = document.getElementById('auth-mode-input');
  if (!regFields) return;

  var activeStyle = 'flex:1;border:none;border-radius:0;padding:10px;font-size:0.9rem;font-weight:600;background:var(--accent,#00d4aa);color:#fff;cursor:pointer';
  var inactiveStyle = 'flex:1;border:none;border-radius:0;padding:10px;font-size:0.9rem;font-weight:500;background:var(--surface-hover,#f8fafc);color:var(--text-muted,#64748b);cursor:pointer';

  if (mode === 'login') {
    regFields.style.display = 'none';
    loginFields.style.display = 'block';
    loginTab.style.cssText = activeStyle;
    regTab.style.cssText = inactiveStyle;
    modeInput.value = 'login';
  } else {
    regFields.style.display = 'block';
    loginFields.style.display = 'none';
    regTab.style.cssText = activeStyle;
    loginTab.style.cssText = inactiveStyle;
    modeInput.value = 'register';
  }
}
(function() {
  var initialMode = document.getElementById('auth-mode-input');
  if (initialMode && initialMode.value === 'login') toggleAuthTab('login');
})();

(function() {
  var container = document.getElementById('product-images-container');
  var addBtn = document.getElementById('add-more-product-images');
  var summaryEl = document.getElementById('product-images-summary');
  if (!container || !addBtn) return;

  function countFiles() {
    var inputs = container.querySelectorAll('input[name="product_images[]"]');
    var total = 0;
    inputs.forEach(function(inp) { total += (inp.files && inp.files.length) ? inp.files.length : 0; });
    return total;
  }
  function updateSummary() {
    var n = countFiles();
    if (summaryEl) {
      summaryEl.style.display = n ? 'block' : 'none';
      summaryEl.textContent = n === 1 ? '1 image selected' : n + ' images selected';
    }
  }

  addBtn.addEventListener('click', function() {
    var input = document.createElement('input');
    input.type = 'file';
    input.name = 'product_images[]';
    input.accept = 'image/jpeg,image/png,image/gif,image/webp';
    input.multiple = true;
    input.className = 'form-input form-input--file product-images-input';
    input.style.marginTop = '10px';
    input.addEventListener('change', updateSummary);
    container.appendChild(input);
  });

  container.addEventListener('change', function(e) {
    if (e.target.name === 'product_images[]') updateSummary();
  });

  var foundersContainer = document.getElementById('founders-container');
  var addFounderBtn = document.getElementById('add-founder-btn');
  if (foundersContainer && addFounderBtn) {
    addFounderBtn.addEventListener('click', function() {
      var row = document.createElement('div');
      row.className = 'founder-row';
      row.style.cssText = 'display: flex; gap: 10px; align-items: flex-start; margin-bottom: 10px;';
      row.innerHTML = '<input type="text" name="founder_names[]" class="form-input" placeholder="e.g. Jane Doe" style="flex: 1;"> <button type="button" class="btn btn-ghost founder-remove" aria-label="Remove founder" style="flex-shrink: 0; padding: 10px 14px;" title="Remove founder"><i class="fa-solid fa-trash-can"></i></button>';
      foundersContainer.appendChild(row);
      row.querySelector('.founder-remove').addEventListener('click', function() {
        var rows = foundersContainer.querySelectorAll('.founder-row');
        if (rows.length > 1) row.remove();
      });
    });
    foundersContainer.querySelectorAll('.founder-remove').forEach(function(btn) {
      btn.addEventListener('click', function() {
        var rows = foundersContainer.querySelectorAll('.founder-row');
        if (rows.length > 1) btn.closest('.founder-row').remove();
      });
    });
  }
})();
</script>
<?php if (!auth()->check()): ?>
<script>
(function() {
  setTimeout(function() {
    if (typeof edenPromoToast === 'function') {
      edenPromoToast({ key: 'submit_guest', message: 'Create an account to manage your listing and track performance.', ctaText: 'Sign up', ctaHref: typeof edenSignupUrl !== 'undefined' ? edenSignupUrl : '/register' });
    }
  }, 2000);
})();
</script>
<?php endif; ?>

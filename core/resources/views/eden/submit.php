<section class="page-head page-head--submit">
  <div class="wrap">
    <h1>Submit your startup</h1>
    <p>Get listed in under 2 minutes. Free for a basic listing.</p>
  </div>
</section>

<div class="wrap content-block submit-page">
  <div class="submit-form-wrap">
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
          <textarea id="description" name="description" class="form-textarea" placeholder="What does your startup do? Who is it for?" rows="4" required><?= e(old('description')) ?></textarea>
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
          <label class="form-label" for="founder_name">Founder name</label>
          <input type="text" id="founder_name" name="founder_name" class="form-input" placeholder="e.g. Jane Doe" value="<?= e(old('founder_name')) ?>">
        </div>
      </div>

      <div class="submit-form-section">
        <h2 class="submit-form-section-title">Media <span class="submit-form-optional">optional</span></h2>
        <div class="form-row form-row--2">
          <div class="form-group">
            <label class="form-label" for="logo">Startup logo</label>
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

      <div class="submit-form-actions">
        <button type="submit" class="btn btn-primary btn--submit"><i class="fa-solid fa-paper-plane" aria-hidden="true"></i> Submit startup</button>
        <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Cancel</a>
      </div>
    </form>
  </div>
</div>
<script>
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
})();
</script>

<section class="page-head">
  <div class="wrap">
    <h1>Submit your startup</h1>
    <p>Get listed in under 2 minutes. Free for a basic listing.</p>
  </div>
</section>

<div class="wrap content-block">
  <form class="form-max" action="<?= e(url('/submit')) ?>" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="_token" value="<?= e(csrf_token()) ?>">
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
      <textarea id="description" name="description" class="form-textarea" placeholder="What does your startup do? Who is it for?" required><?= e(old('description')) ?></textarea>
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
    <div class="form-group">
      <label class="form-label" for="website">Website</label>
      <input type="url" id="website" name="website" class="form-input" placeholder="https://…" value="<?= e(old('website')) ?>" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="location">Location</label>
      <input type="text" id="location" name="location" class="form-input" placeholder="e.g. Harare, Remote" value="<?= e(old('location')) ?>">
    </div>
    <div class="form-group">
      <label class="form-label" for="founder_name">Founder name</label>
      <input type="text" id="founder_name" name="founder_name" class="form-input" placeholder="e.g. Jane Doe" value="<?= e(old('founder_name')) ?>">
    </div>
    <div class="form-group">
      <label class="form-label" for="logo">Startup logo (optional)</label>
      <input type="file" id="logo" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="form-input">
    </div>
    <div class="form-group">
      <label class="form-label">Product images (optional)</label>
      <input type="file" name="product_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple class="form-input">
    </div>
    <div class="form-group">
      <label class="form-label" for="launch-date">Launching today?</label>
      <select id="launch-date" name="launch_today" class="form-select">
        <option value="">No</option>
        <option value="today"<?= old('launch_today') === 'today' ? ' selected' : '' ?>>Yes, we're launching today</option>
      </select>
      <p class="form-hint">If yes, your startup will appear on the Launching today page.</p>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Submit</button>
      <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

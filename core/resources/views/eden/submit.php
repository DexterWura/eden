<section class="page-head">
  <div class="wrap">
    <h1>Submit your startup</h1>
    <p>Get listed in under 2 minutes. Free for a basic listing.</p>
  </div>
</section>

<div class="wrap content-block">
  <form class="form-max" action="#" method="get">
    <div class="form-group">
      <label class="form-label" for="startup-name">Startup name</label>
      <input type="text" id="startup-name" class="form-input" placeholder="e.g. Nexus Pay" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="tagline">Short tagline</label>
      <input type="text" id="tagline" class="form-input" placeholder="One line that describes your startup" maxlength="120">
      <p class="form-hint">Max 120 characters. Shown on your card.</p>
    </div>
    <div class="form-group">
      <label class="form-label" for="description">Description</label>
      <textarea id="description" class="form-textarea" placeholder="What does your startup do? Who is it for?" required></textarea>
    </div>
    <div class="form-group">
      <label class="form-label" for="category">Category</label>
      <select id="category" class="form-select" required>
        <option value="">Choose a category…</option>
        <option value="fintech">Fintech</option>
        <option value="health">Health</option>
        <option value="ai">AI & ML</option>
        <option value="saas">SaaS</option>
        <option value="marketplace">Marketplace</option>
        <option value="edtech">EdTech</option>
        <option value="climate">Climate</option>
        <option value="agtech">AgTech</option>
        <option value="other">Other</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" for="website">Website</label>
      <input type="url" id="website" class="form-input" placeholder="https://…" required>
    </div>
    <div class="form-group">
      <label class="form-label" for="location">Location</label>
      <input type="text" id="location" class="form-input" placeholder="e.g. Harare, Remote">
    </div>
    <div class="form-group">
      <label class="form-label" for="launch-date">Launching today?</label>
      <select id="launch-date" class="form-select">
        <option value="">No</option>
        <option value="today">Yes, we're launching today</option>
      </select>
      <p class="form-hint">If yes, your startup will appear on the Launching today page.</p>
    </div>
    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Submit</button>
      <a href="<?= e(url('/')) ?>" class="btn btn-ghost">Cancel</a>
    </div>
  </form>
</div>

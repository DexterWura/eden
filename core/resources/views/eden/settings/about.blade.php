<h1 class="dash-page-title">About page</h1>
<div class="dash-welcome">
  Edit the content of the public About page. Leave a field blank to use the default text.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">About page content</span>
  </div>
  <div class="dash-card-body">
    <form action="{{ route('admin.settings.about') }}" method="post" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label for="about_head_title" class="dash-label">Page title (heading)</label>
          <input type="text" id="about_head_title" name="head_title" value="{{ old('head_title', $about['head_title'] ?? '') }}" class="dash-input" placeholder="e.g. About Eden">
          @error('head_title') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="about_head_subtitle" class="dash-label">Page subtitle</label>
          <input type="text" id="about_head_subtitle" name="head_subtitle" value="{{ old('head_subtitle', $about['head_subtitle'] ?? '') }}" class="dash-input" placeholder="Short tagline under the title">
          @error('head_subtitle') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="about_what_we_do_title" class="dash-label">Section: What we do (title)</label>
          <input type="text" id="about_what_we_do_title" name="what_we_do_title" value="{{ old('what_we_do_title', $about['what_we_do_title'] ?? '') }}" class="dash-input">
        </div>
        <div>
          <label for="about_what_we_do_body" class="dash-label">Section: What we do (body)</label>
          <textarea id="about_what_we_do_body" name="what_we_do_body" rows="3" class="dash-input">{{ old('what_we_do_body', $about['what_we_do_body'] ?? '') }}</textarea>
        </div>
        <div>
          <label for="about_for_founders_title" class="dash-label">Section: For founders (title)</label>
          <input type="text" id="about_for_founders_title" name="for_founders_title" value="{{ old('for_founders_title', $about['for_founders_title'] ?? '') }}" class="dash-input">
        </div>
        <div>
          <label for="about_for_founders_body" class="dash-label">Section: For founders (body)</label>
          <textarea id="about_for_founders_body" name="for_founders_body" rows="3" class="dash-input">{{ old('for_founders_body', $about['for_founders_body'] ?? '') }}</textarea>
        </div>
        <div>
          <label for="about_for_visitors_title" class="dash-label">Section: For visitors (title)</label>
          <input type="text" id="about_for_visitors_title" name="for_visitors_title" value="{{ old('for_visitors_title', $about['for_visitors_title'] ?? '') }}" class="dash-input">
        </div>
        <div>
          <label for="about_for_visitors_body" class="dash-label">Section: For visitors (body)</label>
          <textarea id="about_for_visitors_body" name="for_visitors_body" rows="3" class="dash-input">{{ old('for_visitors_body', $about['for_visitors_body'] ?? '') }}</textarea>
        </div>
        <div>
          <label for="about_guidelines_title" class="dash-label">Section: Guidelines (title)</label>
          <input type="text" id="about_guidelines_title" name="guidelines_title" value="{{ old('guidelines_title', $about['guidelines_title'] ?? '') }}" class="dash-input">
        </div>
        <div>
          <label for="about_guidelines_items" class="dash-label">Guidelines (one per line)</label>
          <textarea id="about_guidelines_items" name="guidelines_items" rows="4" class="dash-input" placeholder="Your startup must be real and operational...">{{ old('guidelines_items', isset($about['guidelines_items']) && is_array($about['guidelines_items']) ? implode("\n", $about['guidelines_items']) : '') }}</textarea>
          <span class="dash-hint">Each line becomes one bullet point.</span>
        </div>
        <div>
          <label for="about_cta_title" class="dash-label">CTA strip title</label>
          <input type="text" id="about_cta_title" name="cta_title" value="{{ old('cta_title', $about['cta_title'] ?? '') }}" class="dash-input" placeholder="Ready to list your startup?">
        </div>
        <div>
          <label for="about_cta_subtitle" class="dash-label">CTA strip subtitle</label>
          <input type="text" id="about_cta_subtitle" name="cta_subtitle" value="{{ old('cta_subtitle', $about['cta_subtitle'] ?? '') }}" class="dash-input" placeholder="Submit in under 2 minutes.">
        </div>
        <div>
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save About page</button>
        </div>
      </div>
    </form>
  </div>
</div>

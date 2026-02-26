<h1 class="dash-page-title">Settings</h1>
<div class="dash-welcome">
  Application and cache settings.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Cache</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 12px;">Clear the application cache if you changed config or need a fresh state.</p>
    <form action="{{ route('admin.cache.clear') }}" method="post" style="display: inline;">
      @csrf
      <button type="submit" class="dash-btn dash-btn-primary">
        <i class="fa-solid fa-broom"></i> Clear cache
      </button>
    </form>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">SEO &amp; social</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Set meta keywords, meta description, social description and image used when the site is shared (e.g. Open Graph, Twitter cards).</p>
    <form action="{{ route('admin.settings.seo') }}" method="post" enctype="multipart/form-data" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label for="meta_keywords" class="dash-label">Meta keywords</label>
          <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $seo->meta_keywords ?? '') }}" class="dash-input" placeholder="startup directory, startups, launch, ...">
          @error('meta_keywords') <span class="dash-error">{{ $message }}</span> @enderror
          <span class="dash-hint">Comma-separated keywords for search engines.</span>
        </div>
        <div>
          <label for="meta_description" class="dash-label">Meta description</label>
          <textarea id="meta_description" name="meta_description" rows="2" class="dash-input" placeholder="Short description for search results (e.g. 150–160 chars).">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea>
          @error('meta_description') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="social_description" class="dash-label">Social description</label>
          <textarea id="social_description" name="social_description" rows="2" class="dash-input" placeholder="Description when shared on social (Open Graph / Twitter).">{{ old('social_description', $seo->social_description ?? '') }}</textarea>
          @error('social_description') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="seo_image" class="dash-label">SEO / social image</label>
          <input type="file" id="seo_image" name="seo_image" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
          @error('seo_image') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 8px;">
            <strong>Dimensions:</strong> 1200×630px recommended (aspect ratio 1.91:1). Min width 600px. Used as <code>og:image</code> and Twitter card when the site is shared.
          </p>
          <div id="seo_image_preview_wrap" class="seo-image-preview-wrap" style="margin-top: 12px;">
            @if(!empty($seo->seo_image))
              <img id="seo_image_preview" src="{{ asset($seo->seo_image) }}" alt="SEO image preview" style="max-width: 320px; width: 100%; height: auto; border-radius: 8px; border: 1px solid #2a2e3d; display: block;">
              <p id="seo_image_preview_hint" class="dash-hint" style="margin-top: 6px;">Current image. Choose a new file above to replace.</p>
            @else
              <img id="seo_image_preview" src="" alt="" style="max-width: 320px; width: 100%; height: auto; border-radius: 8px; border: 1px solid #2a2e3d; display: none;">
              <p id="seo_image_preview_hint" class="dash-hint" style="margin-top: 6px; display: none;"></p>
            @endif
          </div>
        </div>
        <script>
        (function() {
          var input = document.getElementById('seo_image');
          var preview = document.getElementById('seo_image_preview');
          var hint = document.getElementById('seo_image_preview_hint');
          if (!input || !preview) return;
          var initialSrc = preview.src || '';
          var initialHint = hint ? hint.textContent : '';
          input.addEventListener('change', function() {
            var file = this.files && this.files[0];
            if (file && file.type.indexOf('image/') === 0) {
              if (preview._objectUrl) URL.revokeObjectURL(preview._objectUrl);
              var url = URL.createObjectURL(file);
              preview._objectUrl = url;
              preview.src = url;
              preview.alt = 'New image preview';
              preview.style.display = 'block';
              if (hint) { hint.textContent = 'New image selected. Save the form to apply.'; hint.style.display = 'block'; }
            } else {
              if (preview._objectUrl) { URL.revokeObjectURL(preview._objectUrl); preview._objectUrl = null; }
              preview.src = initialSrc;
              preview.style.display = initialSrc ? 'block' : 'none';
              if (hint) { hint.textContent = initialHint || ''; hint.style.display = initialHint ? 'block' : 'none'; }
            }
          });
        })();
        </script>
        <div>
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save SEO settings</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">Google AdSense</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Enable Google AdSense and paste your AdSense script. When turned on, the script is embedded in the <code>&lt;head&gt;</code> of the whole site so Google can verify and serve ads.</p>
    <form action="{{ route('admin.settings.adsense') }}" method="post" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <input type="checkbox" id="adsense_enabled" name="adsense_enabled" value="1" {{ $adsenseEnabled ?? false ? 'checked' : '' }} class="dash-input" style="width: auto;">
          <label for="adsense_enabled" class="dash-label" style="margin-bottom: 0;">Turn on Google AdSense</label>
        </div>
        <div>
          <label for="adsense_script" class="dash-label">AdSense script</label>
          <textarea id="adsense_script" name="adsense_script" rows="6" class="dash-input" placeholder="Paste the script from your AdSense account (e.g. &lt;script async src=...&gt;&lt;/script&gt;)">{{ old('adsense_script', $adsenseScript ?? '') }}</textarea>
          @error('adsense_script') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 8px;">Paste the full script tag(s) from Google AdSense. This will be output in the site header when AdSense is on.</p>
        </div>
        <div>
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save AdSense settings</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">About page</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Edit the content of the public About page. Leave a field blank to use the default text.</p>
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

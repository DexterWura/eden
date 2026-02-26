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
          @if(!empty($seo->seo_image))
            <p style="margin-top: 8px;">
              <img src="{{ asset($seo->seo_image) }}" alt="Current SEO image" style="max-width: 200px; height: auto; border-radius: 6px;">
              <span class="dash-hint" style="display: block;">Current image. Upload a new one to replace (recommended: 1200×630px).</span>
            </p>
          @else
            <span class="dash-hint">Recommended: 1200×630px. Used as og:image and Twitter card.</span>
          @endif
        </div>
        <div>
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save SEO settings</button>
        </div>
      </div>
    </form>
  </div>
</div>

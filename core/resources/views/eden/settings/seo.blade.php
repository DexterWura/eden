<h1 class="dash-page-title">SEO</h1>
<div class="dash-welcome">
  Set meta keywords, meta description, social description and image used when the site is shared (e.g. Open Graph, Twitter cards).
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">SEO &amp; social</span>
  </div>
  <div class="dash-card-body">
    <form action="{{ route('admin.settings.seo') }}" method="post" enctype="multipart/form-data" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label for="meta_keywords" class="dash-label">Meta keywords</label>
          <input type="text" id="meta_keywords" name="meta_keywords" value="{{ old('meta_keywords', $seo->meta_keywords ?? '') }}" class="dash-input" placeholder="app directory, apps, launch, ...">
          @error('meta_keywords') <span class="dash-error">{{ $message }}</span> @enderror
          <span class="dash-hint">Comma-separated keywords for search engines.</span>
        </div>
        <div>
          <label for="meta_description" class="dash-label">Meta description</label>
          <textarea id="meta_description" name="meta_description" rows="2" class="dash-input" maxlength="160" placeholder="Short description for search results. 150–160 chars is optimal for Google.">{{ old('meta_description', $seo->meta_description ?? '') }}</textarea>
          <p class="dash-hint" style="margin-top: 4px;"><span id="meta_desc_count">0</span>/160 — optimal length for Google search snippets.</p>
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
          var metaDesc = document.getElementById('meta_description');
          var countEl = document.getElementById('meta_desc_count');
          if (metaDesc && countEl) {
            function updateCount() { countEl.textContent = (metaDesc.value || '').length; }
            metaDesc.addEventListener('input', updateCount);
            metaDesc.addEventListener('change', updateCount);
            updateCount();
          }
        })();
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

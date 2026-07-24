<h1 class="dash-page-title">{{ $post->id ? 'Edit post' : 'New post' }}</h1>
<div class="dash-welcome">
  {{ $post->id ? 'Update title, body, and SEO fields.' : 'Create a blog post. Fill SEO fields for better search and social sharing.' }}
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">{{ $post->id ? 'Edit post' : 'New post' }}</span>
  </div>
  <div class="dash-card-body">
    <form action="{{ $post->id ? route('admin.blog.update', $post) : route('admin.blog.store') }}" method="post" enctype="multipart/form-data" class="dash-form">
      @csrf
      @if($post->id) @method('PUT') @endif

      <div style="display: flex; flex-direction: column; gap: 20px;">
        <div>
          <label for="title" class="dash-label">Title <span style="color: #dc2626;">*</span></label>
          <input type="text" id="title" name="title" class="dash-input" value="{{ old('title', $post->title) }}" placeholder="Post title" required maxlength="255">
          @error('title') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">Used for the heading and default page title. Slug is auto-generated from this.</p>
        </div>

        <div>
          <label for="excerpt" class="dash-label">Excerpt</label>
          <textarea id="excerpt" name="excerpt" class="dash-input" rows="2" placeholder="Short summary (optional)" maxlength="500">{{ old('excerpt', $post->excerpt) }}</textarea>
          @error('excerpt') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">Brief summary for listings and meta description fallback.</p>
        </div>

        <div>
          <label for="body" class="dash-label">Body <span style="color: #dc2626;">*</span></label>
          <textarea id="body" name="body" class="dash-input" rows="14" placeholder="Post content (HTML allowed)" required>{{ old('body', $post->body) }}</textarea>
          @error('body') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">You can use simple HTML (e.g. &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;a&gt;).</p>
        </div>
        <div>
          <label for="source_urls" class="dash-label">Sources and further reading</label>
          <textarea id="source_urls" name="source_urls" class="dash-input" rows="4" maxlength="10000" placeholder="https://example.com/source">{{ old('source_urls', implode("\n", $post->source_urls ?? [])) }}</textarea>
          <p class="dash-hint" style="margin-top:6px;">One full URL per line. Published below the article to support factual claims.</p>
        </div>

        <hr style="border: 0; border-top: 1px solid var(--d-border); margin: 8px 0;">
        <h3 class="dash-card-title" style="margin-bottom: 4px;">SEO</h3>
        <p class="dash-hint" style="margin-bottom: 12px;">Optional. Leave blank to use title/excerpt as fallbacks.</p>

        <div>
          <label for="meta_title" class="dash-label">Meta title</label>
          <input type="text" id="meta_title" name="meta_title" class="dash-input" value="{{ old('meta_title', $post->meta_title) }}" placeholder="e.g. How to Launch — Eden" maxlength="70">
          @error('meta_title') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">Recommended length 50–60 characters. Shown in search results.</p>
        </div>

        <div>
          <label for="meta_description" class="dash-label">Meta description</label>
          <textarea id="meta_description" name="meta_description" class="dash-input" rows="2" placeholder="Short description for search and social" maxlength="160">{{ old('meta_description', $post->meta_description) }}</textarea>
          @error('meta_description') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">Recommended 150–160 characters.</p>
        </div>

        <div>
          <label for="meta_keywords" class="dash-label">Meta keywords</label>
          <input type="text" id="meta_keywords" name="meta_keywords" class="dash-input" value="{{ old('meta_keywords', $post->meta_keywords) }}" placeholder="app, launch, tips" maxlength="255">
          @error('meta_keywords') <span class="dash-error">{{ $message }}</span> @enderror
        </div>

        <div>
          <label for="og_image" class="dash-label">OG image</label>
          <input type="file" id="og_image" name="og_image" class="dash-input" accept="image/jpeg,image/png,image/webp,image/gif">
          @error('og_image') <span class="dash-error">{{ $message }}</span> @enderror
          @if($post->og_image_path)
            <p class="dash-hint" style="margin-top: 6px;">Current: <a href="{{ asset($post->og_image_path) }}" target="_blank">{{ $post->og_image_path }}</a>. Upload a new image to replace.</p>
          @else
            <p class="dash-hint" style="margin-top: 6px;">Recommended 1200×630px for social sharing.</p>
          @endif
        </div>

        <div>
          <label class="dash-label">Status</label>
          <div style="display: flex; gap: 16px; margin-top: 8px;">
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="status" value="draft" {{ old('status', $post->status) === 'draft' ? 'checked' : '' }}>
              Draft
            </label>
            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
              <input type="radio" name="status" value="published" {{ old('status', $post->status) === 'published' ? 'checked' : '' }}>
              Published
            </label>
          </div>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> {{ $post->id ? 'Save changes' : 'Create post' }}</button>
          <a href="{{ route('admin.blog.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
          @if($post->id && $post->isPublished())
            <a href="{{ url('/blog/' . $post->slug) }}" target="_blank" class="dash-btn dash-btn-secondary" style="text-decoration: none;"><i class="fa-solid fa-external-link-alt"></i> View post</a>
          @endif
        </div>
      </div>
    </form>
  </div>
</div>

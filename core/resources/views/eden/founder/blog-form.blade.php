<h1 class="dash-page-title">{{ $post->exists ? 'Edit post' : 'Write a blog post' }}</h1>
<div class="dash-welcome">
  {{ $post->exists ? 'Update your post title, content, and featured image.' : 'Share updates with the Eden community. HTML is supported in the body.' }}
</div>

@if($errors->any())
<div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b">
  @foreach($errors->all() as $error)
  <p style="margin:0 0 4px">{{ $error }}</p>
  @endforeach
</div>
@endif

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">{{ $post->exists ? 'Edit post' : 'New post' }}</span>
  </div>
  <div class="dash-card-body">
    <form action="{{ $post->exists ? route('founder.blog.update', $post) : route('founder.blog.store') }}" method="POST" enctype="multipart/form-data" class="dash-form">
      @csrf
      @if($post->exists) @method('PUT') @endif

      <div style="display: flex; flex-direction: column; gap: 20px;">
        <div>
          <label for="title" class="dash-label">Title <span style="color: #dc2626;">*</span></label>
          <input type="text" name="title" id="title" class="dash-input" value="{{ old('title', $post->title) }}" required maxlength="255" placeholder="Your post title">
          @error('title') <span class="dash-error">{{ $message }}</span> @enderror
        </div>

        <div>
          <label for="excerpt" class="dash-label">Excerpt <span class="dash-hint" style="font-weight: normal;">(optional)</span></label>
          <textarea name="excerpt" id="excerpt" class="dash-input" rows="2" maxlength="500" placeholder="Short summary shown in listings">{{ old('excerpt', $post->excerpt) }}</textarea>
          @error('excerpt') <span class="dash-error">{{ $message }}</span> @enderror
        </div>

        <div>
          <label for="og_image" class="dash-label">Featured image</label>
          <input type="file" id="og_image" name="og_image" class="dash-input" accept="image/jpeg,image/png,image/webp,image/gif">
          @error('og_image') <span class="dash-error">{{ $message }}</span> @enderror
          @if($post->og_image_path)
            <p class="dash-hint" style="margin-top: 8px;">Current image:</p>
            <img src="{{ asset($post->og_image_path) }}" alt="" style="max-width: 320px; width: 100%; border-radius: 8px; border: 1px solid var(--d-border); margin-top: 8px;">
          @else
            <p class="dash-hint" style="margin-top: 6px;">Recommended 1200×630px. Shown on the blog listing and at the top of your post.</p>
          @endif
        </div>

        <div>
          <label for="body" class="dash-label">Content <span style="color: #dc2626;">*</span></label>
          <textarea name="body" id="body" class="dash-input" rows="16" required placeholder="Write your post content here…">{{ old('body', $post->body) }}</textarea>
          @error('body') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 6px;">You can use simple HTML (e.g. &lt;p&gt;, &lt;h2&gt;, &lt;ul&gt;, &lt;a&gt;, &lt;img&gt;).</p>
        </div>

        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
          <button type="submit" class="dash-btn dash-btn-primary">{{ $post->exists ? 'Update post' : 'Publish post' }}</button>
          <a href="{{ route('founder.blog.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
          @if($post->exists && $post->isPublished())
            <a href="{{ url('/blog/' . $post->slug) }}" target="_blank" class="dash-btn dash-btn-secondary" style="text-decoration: none;">View post</a>
          @endif
        </div>
      </div>
    </form>
  </div>
</div>

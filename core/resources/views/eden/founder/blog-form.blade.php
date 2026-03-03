<h1 class="dash-page-title">{{ $post->exists ? 'Edit post' : 'Write a blog post' }}</h1>

@if($errors->any())
<div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b">
  @foreach($errors->all() as $error)
  <p style="margin:0 0 4px">{{ $error }}</p>
  @endforeach
</div>
@endif

<div class="dash-card">
  <div class="dash-card-body">
    <form action="{{ $post->exists ? route('founder.blog.update', $post) : route('founder.blog.store') }}" method="POST">
      @csrf
      @if($post->exists) @method('PUT') @endif

      <div class="form-group" style="margin-bottom:20px">
        <label class="form-label" for="title">Title</label>
        <input type="text" name="title" id="title" class="form-input" value="{{ old('title', $post->title) }}" required maxlength="255" placeholder="Your post title">
      </div>

      <div class="form-group" style="margin-bottom:20px">
        <label class="form-label" for="excerpt">Excerpt <span style="color:var(--d-text-secondary);font-weight:normal">(optional)</span></label>
        <input type="text" name="excerpt" id="excerpt" class="form-input" value="{{ old('excerpt', $post->excerpt) }}" maxlength="500" placeholder="Short summary shown in listings">
      </div>

      <div class="form-group" style="margin-bottom:20px">
        <label class="form-label" for="body">Content</label>
        <textarea name="body" id="body" class="form-input" rows="16" required placeholder="Write your post content here… HTML is supported.">{{ old('body', $post->body) }}</textarea>
      </div>

      <div style="display:flex;gap:12px;flex-wrap:wrap">
        <button type="submit" class="dash-btn dash-btn-primary">{{ $post->exists ? 'Update post' : 'Publish post' }}</button>
        <a href="{{ route('founder.blog.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration:none">Cancel</a>
      </div>
    </form>
  </div>
</div>

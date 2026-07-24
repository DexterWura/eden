<h1 class="dash-page-title">Blog</h1>
<div class="dash-welcome">
  Manage SEO-rich blog posts. Set meta title, description, and OG image for each post.
</div>

<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Published</div>
    <div class="dash-kpi-value">{{ $countPublished }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Drafts</div>
    <div class="dash-kpi-value">{{ $countDraft }}</div>
  </div>
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">All posts</span>
    <a href="{{ route('admin.blog.create') }}" class="dash-btn dash-btn-primary" style="margin-left: auto;">
      <i class="fa-solid fa-plus"></i> New post
    </a>
  </div>
  <div class="dash-card-body">
    <form method="get" action="{{ route('admin.blog.index') }}" class="dash-startups-filters" style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 16px;">
      <input type="text" name="q" value="{{ $search }}" placeholder="Search title, excerpt…" class="dash-search" style="max-width: 260px;">
      <select name="status" class="dash-search" style="max-width: 160px;">
        <option value="">All</option>
        <option value="published" {{ $statusFilter === 'published' ? 'selected' : '' }}>Published</option>
        <option value="draft" {{ $statusFilter === 'draft' ? 'selected' : '' }}>Draft</option>
      </select>
      <button type="submit" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-magnifying-glass"></i> Filter</button>
    </form>
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Published</th>
            <th>Updated</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($posts as $post)
          <tr>
            <td>
              <a href="{{ $post->isPublished() ? url('/blog/' . $post->slug) : '#' }}" target="_blank" class="dash-table-link">{{ $post->title }}</a>
              @if($post->excerpt)
                <div style="font-size: 0.8rem; color: var(--d-text-secondary); margin-top: 2px;">{{ Str::limit($post->excerpt, 60) }}</div>
              @endif
            </td>
            <td><code>{{ $post->slug }}</code></td>
            <td>
              @if($post->status === 'published')
                <span class="dash-badge dash-badge-success">Published</span>
              @else
                <span class="dash-badge dash-badge-warning">Draft</span>
              @endif
            </td>
            <td>{{ $post->published_at ? $post->published_at->format('M j, Y') : '—' }}</td>
            <td>{{ $post->updated_at->format('M j, Y H:i') }}</td>
            <td>
              <a href="{{ route('admin.blog.edit', $post) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;"><i class="fa-solid fa-pen"></i> Edit</a>
              <form action="{{ route('admin.blog.destroy', $post) }}" method="post" style="display: inline;" data-confirm="Delete this blog post permanently?" data-confirm-label="Delete post">
                @csrf
                @method('DELETE')
                <button type="submit" class="dash-btn" style="padding: 4px 10px; font-size: 0.8rem; background: #dc2626; color: #fff; border: none;">Delete</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="dash-placeholder">No posts yet. <a href="{{ route('admin.blog.create') }}">Create one</a>.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if($posts->hasPages())
      <div class="dash-card-footer" style="padding: 12px 16px; border-top: 1px solid var(--d-border);">
        {{ $posts->links() }}
      </div>
    @endif
  </div>
</div>

<style>
.dash-badge { display: inline-block; padding: 2px 8px; font-size: 0.75rem; border-radius: 4px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-warning { background: #fef3c7; color: #92400e; }
</style>

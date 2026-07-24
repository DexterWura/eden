<h1 class="dash-page-title">My blog posts</h1>
<div class="dash-welcome">
  Share updates, milestones, and stories about your app.
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Your posts</span>
    <a href="{{ route('founder.blog.create') }}" class="dash-btn dash-btn-primary" style="margin-left: auto; text-decoration: none;">
      <i class="fa-solid fa-plus"></i> Write post
    </a>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Title</th>
            <th>Status</th>
            <th>Published</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($posts as $post)
          <tr>
            <td>
              <a href="{{ url('/blog/' . $post->slug) }}" target="_blank" class="dash-table-link">{{ $post->title }}</a>
            </td>
            <td>{{ ucfirst($post->status) }}</td>
            <td>{{ $post->published_at ? $post->published_at->format('M d, Y') : '—' }}</td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <a href="{{ route('founder.blog.edit', $post) }}" class="dash-btn dash-btn-secondary" style="padding:4px 10px;font-size:0.8rem;text-decoration:none"><i class="fa-solid fa-pen"></i> Edit</a>
              <form action="{{ route('founder.blog.destroy', $post) }}" method="POST" style="display:inline" onsubmit="return confirm('Delete this post?')">
                @csrf @method('DELETE')
                <button type="submit" class="dash-btn dash-btn-danger" style="padding:4px 10px;font-size:0.8rem"><i class="fa-solid fa-trash"></i></button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="dash-placeholder">No blog posts yet. <a href="{{ route('founder.blog.create') }}">Write your first post</a>.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

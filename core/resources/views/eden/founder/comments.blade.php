<h1 class="dash-page-title">Comment inbox</h1>
<p class="dash-welcome">Reply to comments on startups you manage and track what still needs attention.</p>

<div style="display:flex;flex-direction:column;gap:14px;">
@forelse($comments as $comment)
  <article class="dash-card">
    <div class="dash-card-header">
      <div>
        <span class="dash-card-title">{{ $comment->startup->name }}</span>
        <span class="dash-card-subtitle">{{ $comment->user->name ?? 'User' }} · {{ $comment->created_at->diffForHumans() }}</span>
      </div>
      <span class="dash-badge {{ $comment->addressed_at ? 'dash-badge-success' : 'dash-badge-warning' }}">{{ $comment->addressed_at ? 'Addressed' : 'Needs reply' }}</span>
    </div>
    <div class="dash-card-body">
      <p style="white-space:pre-wrap;">{{ $comment->body }}</p>
      @if($comment->founder_reply)
      <div style="padding:12px;border-left:3px solid var(--d-primary);background:var(--d-surface);margin:12px 0;">
        <strong>Founder reply</strong>
        <p style="white-space:pre-wrap;margin-bottom:0;">{{ $comment->founder_reply }}</p>
      </div>
      @endif
      <form action="{{ route('founder.comments.reply', $comment) }}" method="post" class="dash-form" style="display:flex;flex-direction:column;gap:8px;">
        @csrf
        <label class="dash-label" for="reply-{{ $comment->id }}">{{ $comment->founder_reply ? 'Update public reply' : 'Public reply' }}</label>
        <textarea id="reply-{{ $comment->id }}" name="reply" rows="3" maxlength="2000" required class="dash-input">{{ old('reply', $comment->founder_reply) }}</textarea>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <button class="dash-btn dash-btn-primary" type="submit">Publish reply</button>
          <a class="dash-btn dash-btn-secondary" href="{{ route('startup.show', $comment->startup->slug) }}#comments" target="_blank" style="text-decoration:none;">View public thread</a>
        </div>
      </form>
      <form action="{{ route('founder.comments.addressed', $comment) }}" method="post" style="margin-top:8px;">
        @csrf
        @method('PATCH')
        <input type="hidden" name="addressed" value="{{ $comment->addressed_at ? 0 : 1 }}">
        <button class="dash-btn dash-btn-secondary" type="submit">{{ $comment->addressed_at ? 'Reopen' : 'Mark addressed without reply' }}</button>
      </form>
    </div>
  </article>
@empty
  <div class="dash-card"><div class="dash-card-body"><p class="dash-placeholder">No comments on your startups yet.</p></div></div>
@endforelse
</div>

@if($comments->hasPages())
<nav aria-label="Comment pages" style="margin-top:16px;">{{ $comments->links() }}</nav>
@endif

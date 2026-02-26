<h1 class="dash-page-title">Contact messages</h1>
<div class="dash-welcome">
  Messages sent from the contact form on the site.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">All messages</span>
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Name</th>
            <th>Email</th>
            <th>Subject</th>
            <th>Message</th>
          </tr>
        </thead>
        <tbody>
          @forelse($messages as $m)
          <tr>
            <td style="white-space: nowrap;">{{ $m->created_at->format('M j, Y H:i') }}</td>
            <td>{{ $m->name }}</td>
            <td><a href="mailto:{{ $m->email }}">{{ $m->email }}</a></td>
            <td>{{ $m->subject ? ucfirst($m->subject) : '—' }}</td>
            <td style="max-width: 280px;">{{ Str::limit($m->message, 80) }}</td>
          </tr>
          @empty
          <tr>
            <td colspan="5" class="dash-placeholder">No contact messages yet.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  @if($messages->hasPages())
  <div class="dash-card-footer" style="padding: 12px 16px; border-top: 1px solid var(--d-border);">
    {{ $messages->links() }}
  </div>
  @endif
</div>

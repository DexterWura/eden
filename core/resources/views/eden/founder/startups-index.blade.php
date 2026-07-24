<h1 class="dash-page-title">My apps</h1>
<div class="dash-welcome">
  Apps linked to your account. Add a new one or edit details.
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Your apps</span>
    @if($canAddStartup ?? true)
    <a href="{{ route('founder.startups.create') }}" class="dash-btn dash-btn-primary" style="margin-left: auto; text-decoration: none;">
      <i class="fa-solid fa-plus"></i> Add app
    </a>
    @else
    <a href="{{ url('/pricing') }}" class="dash-btn dash-btn-primary" style="margin-left: auto; text-decoration: none;">
      <i class="fa-solid fa-crown"></i> Upgrade to Pro for more apps
    </a>
    @endif
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>App</th>
            <th>Category</th>
            <th>Upvotes</th>
            <th>Status</th>
            <th>Growth status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($startups as $s)
          @php($profile = ($startupProfiles ?? collect())->get($s->id))
          <tr>
            <td>
              <a href="{{ url('/startup/' . $s->slug) }}" target="_blank" class="dash-table-link">{{ $s->name }}</a>
              @if($s->tagline)
                <div style="font-size: 0.8rem; color: var(--d-text-secondary);">{{ Str::limit($s->tagline, 50) }}</div>
              @endif
            </td>
            <td>{{ $s->category ?? '—' }}</td>
            <td>{{ $s->upvotes }}</td>
            <td>
              @if($s->status === 'pending')
                <span style="display:inline-block;padding:2px 8px;font-size:0.75rem;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:600">Pending review</span>
              @else
                {{ $s->status ?? 'active' }}
              @endif
            </td>
            <td>
              @if($profile)
                <div>{{ ucfirst($profile['claimStatus']) }} ownership · {{ $profile['awards']->count() }} awards</div>
                <div style="font-size:.8rem;color:var(--d-text-secondary)">{{ $profile['cofounderStatus'] }} co-founder invites · {{ $profile['investorStatus'] }} new investor leads</div>
                <div style="font-size:.8rem;color:var(--d-text-secondary)">
                  @if($profile['daysUntilLaunch'] !== null) Launch in {{ $profile['daysUntilLaunch'] }} days
                  @elseif($profile['launchDate']) Launched {{ $profile['launchDate']->format('M j, Y') }}
                  @else Launch date not set
                  @endif
                  · {{ $profile['launchReadiness'] === 'ready' ? 'Ready' : 'Needs attention' }}
                </div>
              @endif
            </td>
            <td style="display:flex;gap:6px;flex-wrap:wrap;align-items:center">
              <a href="{{ route('founder.startups.edit', $s) }}" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem; text-decoration: none;"><i class="fa-solid fa-pen"></i> Edit</a>
              @if(auth()->user()->isPro())
              <form action="{{ route('founder.startups.toggle-featured', $s) }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="dash-btn {{ $s->is_featured ? 'dash-btn-primary' : 'dash-btn-secondary' }}" style="padding:4px 10px;font-size:0.8rem" title="{{ $s->is_featured ? 'Remove from featured' : 'Feature this app' }}">
                  <i class="fa-solid fa-star"></i> {{ $s->is_featured ? 'Featured' : 'Feature' }}
                </button>
              </form>
              <form action="{{ route('founder.startups.destroy', $s) }}" method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to delete this app? This cannot be undone.')">
                @csrf @method('DELETE')
                <button type="submit" class="dash-btn dash-btn-danger" style="padding:4px 10px;font-size:0.8rem"><i class="fa-solid fa-trash"></i> Delete</button>
              </form>
              @else
              <a href="{{ url('/pricing') }}" class="dash-btn dash-btn-secondary" style="padding:4px 10px;font-size:0.8rem;text-decoration:none;opacity:0.6" title="Pro feature"><i class="fa-solid fa-crown"></i> Pro</a>
              @endif
              @if($profile)
              <a href="{{ $profile['sharePreview']['xShareUrl'] }}" target="_blank" rel="noopener" class="dash-btn dash-btn-secondary" style="padding:4px 10px;font-size:.8rem;text-decoration:none"><i class="fa-brands fa-x-twitter"></i> Share</a>
              @endif
            </td>
          </tr>
          <tr>
            <td colspan="6">
              <form action="{{ route('founder.cofounder-invitations.store', $s) }}" method="POST" style="display:flex;gap:8px;align-items:center;max-width:620px">
                @csrf
                <label for="cofounder-email-{{ $s->id }}" class="dash-label" style="margin:0;white-space:nowrap">Invite co-founder</label>
                <input id="cofounder-email-{{ $s->id }}" type="email" name="email" class="dash-input" maxlength="255" required placeholder="cofounder@example.com">
                <button class="dash-btn dash-btn-secondary" type="submit">Send invite</button>
              </form>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="dash-placeholder">No apps yet. @if($canAddStartup ?? true)<a href="{{ route('founder.startups.create') }}">Add your first app</a>@else<a href="{{ url('/pricing') }}">Upgrade to Pro</a> to add apps.@endif</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

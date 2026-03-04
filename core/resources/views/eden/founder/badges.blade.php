<h1 class="dash-page-title">Badges</h1>
<div class="dash-welcome">
  Embed these badges on your site to show that your startup is listed on {{ $siteName }}. Copy the HTML and paste it where you want the badge to appear. Each badge links to your startup page.
</div>

@if($startups->isEmpty())
<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-body">
    <p class="dash-placeholder">You have no startups yet. <a href="{{ route('founder.startups.create') }}">Add a startup</a> to get badges.</p>
  </div>
</div>
@else
@foreach($startups as $startup)
@php
  $startupUrl = url('/startup/' . $startup->slug);
  $isFeatured = $startup->is_featured ?? false;
  $isProductOfDay = $productOfDayId !== null && (int) $startup->id === (int) $productOfDayId;
@endphp
<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">{{ $startup->name }}</span>
    <a href="{{ $startupUrl }}" target="_blank" rel="noopener" class="dash-table-link">View on {{ $siteName }}</a>
  </div>
  <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 20px;">
    {{-- Listed on Eden (always) --}}
    <div class="badge-row">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/listed" alt="Listed on {{ $siteName }}" width="200" height="32" style="border: 0; display: block;">
        <span class="dash-badge dash-badge-success">Always available</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Listed on {{ $siteName }})</label>
      <pre class="dash-badge-code"><code>&lt;a href="{{ $startupUrl }}" target="_blank" rel="noopener"&gt;&lt;img src="{{ $badgeBaseUrl }}/listed" alt="Listed on {{ $siteName }}" width="200" height="32" style="border:0;"&gt;&lt;/a&gt;</code></pre>
    </div>

    @if($isFeatured)
    <div class="badge-row">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/featured" alt="Featured on {{ $siteName }}" width="200" height="32" style="border: 0; display: block;">
        <span class="dash-badge dash-badge-success">Featured</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Featured on {{ $siteName }})</label>
      <pre class="dash-badge-code"><code>&lt;a href="{{ $startupUrl }}" target="_blank" rel="noopener"&gt;&lt;img src="{{ $badgeBaseUrl }}/featured" alt="Featured on {{ $siteName }}" width="200" height="32" style="border:0;"&gt;&lt;/a&gt;</code></pre>
    </div>
    @else
    <div class="badge-row badge-row--locked">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/featured" alt="Featured on {{ $siteName }}" width="200" height="32" style="border: 0; display: block; opacity: 0.6;">
        <span class="dash-badge dash-badge-muted">Not featured</span>
      </div>
      <p class="badge-hint">This badge appears when your startup is featured by {{ $siteName }}.</p>
    </div>
    @endif

    @if($isProductOfDay)
    <div class="badge-row">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/product-of-day" alt="Product of the day on {{ $siteName }}" width="200" height="32" style="border: 0; display: block;">
        <span class="dash-badge dash-badge-success">Product of the day</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Product of the day on {{ $siteName }})</label>
      <pre class="dash-badge-code"><code>&lt;a href="{{ $startupUrl }}" target="_blank" rel="noopener"&gt;&lt;img src="{{ $badgeBaseUrl }}/product-of-day" alt="Product of the day on {{ $siteName }}" width="200" height="32" style="border:0;"&gt;&lt;/a&gt;</code></pre>
    </div>
    @else
    <div class="badge-row badge-row--locked">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/product-of-day" alt="Product of the day on {{ $siteName }}" width="200" height="32" style="border: 0; display: block; opacity: 0.6;">
        <span class="dash-badge dash-badge-muted">Not product of the day</span>
      </div>
      <p class="badge-hint">This badge appears when your startup is product of the day (top by upvotes).</p>
    </div>
    @endif
  </div>
</div>
@endforeach
@endif

<style>
.badge-row { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--d-border, #e5e7eb); }
.badge-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.badge-row--locked { opacity: 0.75; }
.badge-preview { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; flex-wrap: wrap; }
.badge-preview img { border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.08); }
.badge-code-label { font-size: 0.8rem; color: var(--d-text-secondary); }
.badge-hint { margin: 0; font-size: 0.8125rem; color: var(--d-text-secondary); }
.dash-badge-code { margin: 8px 0 0; padding: 12px 14px; background: var(--d-bg); border: 1px solid var(--d-border); border-radius: 8px; font-size: 0.75rem; overflow-x: auto; cursor: pointer; transition: border-color 0.15s, box-shadow 0.15s; }
.dash-badge-code:hover { border-color: var(--accent, #0d9488); box-shadow: 0 0 0 1px var(--accent, #0d9488); }
.dash-badge-code code { white-space: pre-wrap; word-break: break-all; font-family: ui-monospace, monospace; }
.dash-badge { display: inline-block; padding: 3px 10px; font-size: 0.75rem; font-weight: 600; border-radius: 6px; }
.dash-badge-success { background: #d1fae5; color: #065f46; }
.dash-badge-muted { background: #e5e7eb; color: #374151; }
</style>

<script>
(function() {
  document.querySelectorAll('.dash-badge-code').forEach(function(pre) {
    pre.style.cursor = 'pointer';
    pre.title = 'Click to select';
    pre.addEventListener('click', function() {
      var code = pre.querySelector('code');
      if (!code) return;
      var range = document.createRange();
      range.selectNodeContents(code);
      var sel = window.getSelection();
      sel.removeAllRanges();
      sel.addRange(range);
    });
  });
})();
</script>

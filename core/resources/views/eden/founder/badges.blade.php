<h1 class="dash-page-title">Badges</h1>
<div class="dash-welcome">
  Embed these badges on your site to show that your startup is listed on {{ $siteName }}. Copy the HTML and paste it where you want the badge to appear. Each badge links to your startup page.
</div>
<div class="badge-dofollow-note">All embed snippets below are generated as dofollow links.</div>
@php
  $defaultTheme = request('theme') === 'light' ? 'light' : 'dark';
@endphp

@if($startups->isEmpty())
<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-body">
    <p class="dash-placeholder">You have no startups yet. <a href="{{ route('founder.startups.create') }}">Add a startup</a> to get badges.</p>
  </div>
</div>
@else
<div class="badge-theme-picker">
  <span class="badge-theme-picker__label">Badge style:</span>
  <label class="badge-theme-option">
    <input type="radio" name="badge-theme" value="dark" @checked($defaultTheme === 'dark')>
    <span>Dark</span>
  </label>
  <label class="badge-theme-option">
    <input type="radio" name="badge-theme" value="light" @checked($defaultTheme === 'light')>
    <span>Light</span>
  </label>
</div>
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
        <img
          src="{{ $badgeBaseUrl }}/listed?theme={{ $defaultTheme }}"
          alt="Listed on {{ $siteName }}"
          width="220"
          height="52"
          style="border: 0; display: block;"
          class="badge-preview-image"
          data-badge-src="{{ $badgeBaseUrl }}/listed">
        <span class="dash-badge dash-badge-success">Always available</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Listed on {{ $siteName }})</label>
      <pre class="dash-badge-code"><code class="badge-embed-code" data-startup-url="{{ $startupUrl }}" data-badge-src="{{ $badgeBaseUrl }}/listed" data-alt="Listed on {{ $siteName }}"></code></pre>
    </div>

    @if($isFeatured)
    <div class="badge-row">
      <div class="badge-preview">
        <img
          src="{{ $badgeBaseUrl }}/featured?theme={{ $defaultTheme }}"
          alt="Featured on {{ $siteName }}"
          width="220"
          height="52"
          style="border: 0; display: block;"
          class="badge-preview-image"
          data-badge-src="{{ $badgeBaseUrl }}/featured">
        <span class="dash-badge dash-badge-success">Featured</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Featured on {{ $siteName }})</label>
      <pre class="dash-badge-code"><code class="badge-embed-code" data-startup-url="{{ $startupUrl }}" data-badge-src="{{ $badgeBaseUrl }}/featured" data-alt="Featured on {{ $siteName }}"></code></pre>
    </div>
    @else
    <div class="badge-row badge-row--locked">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/featured?theme={{ $defaultTheme }}" alt="Featured on {{ $siteName }}" width="220" height="52" style="border: 0; display: block; opacity: 0.55;" class="badge-preview-image" data-badge-src="{{ $badgeBaseUrl }}/featured">
        <span class="dash-badge dash-badge-muted">Not featured</span>
      </div>
      <p class="badge-hint">This badge appears when your startup is featured by {{ $siteName }}.</p>
    </div>
    @endif

    @if($isProductOfDay)
    <div class="badge-row">
      <div class="badge-preview">
        <img
          src="{{ $badgeBaseUrl }}/product-of-day?theme={{ $defaultTheme }}"
          alt="Product of the day on {{ $siteName }}"
          width="220"
          height="52"
          style="border: 0; display: block;"
          class="badge-preview-image"
          data-badge-src="{{ $badgeBaseUrl }}/product-of-day">
        <span class="dash-badge dash-badge-success">Product of the day</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Product of the day on {{ $siteName }})</label>
      <pre class="dash-badge-code"><code class="badge-embed-code" data-startup-url="{{ $startupUrl }}" data-badge-src="{{ $badgeBaseUrl }}/product-of-day" data-alt="Product of the day on {{ $siteName }}"></code></pre>
    </div>
    @else
    <div class="badge-row badge-row--locked">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/product-of-day?theme={{ $defaultTheme }}" alt="Product of the day on {{ $siteName }}" width="220" height="52" style="border: 0; display: block; opacity: 0.55;" class="badge-preview-image" data-badge-src="{{ $badgeBaseUrl }}/product-of-day">
        <span class="dash-badge dash-badge-muted">Not product of the day</span>
      </div>
      <p class="badge-hint">This badge appears when your startup is product of the day (most upvotes that day).</p>
    </div>
    @endif
  </div>
</div>
@endforeach
@endif

<style>
.badge-theme-picker { margin-top: 18px; margin-bottom: 8px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.badge-theme-picker__label { font-size: 0.8125rem; color: var(--d-text-secondary, #64748b); font-weight: 600; }
.badge-theme-option { display: inline-flex; align-items: center; gap: 7px; padding: 6px 11px; border-radius: 999px; border: 1px solid var(--d-border, #e5e7eb); background: var(--d-bg-soft, #fff); font-size: 0.8125rem; cursor: pointer; user-select: none; }
.badge-theme-option input { margin: 0; accent-color: var(--accent, #0f766e); }
.badge-dofollow-note { margin-top: 10px; color: var(--d-text-secondary, #64748b); font-size: 0.8rem; }
.badge-row { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--d-border, #e5e7eb); }
.badge-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
.badge-row--locked { opacity: 0.75; }
.badge-preview { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; flex-wrap: wrap; }
.badge-preview img { border-radius: 26px; box-shadow: 0 7px 14px rgba(15, 23, 42, 0.12); }
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
  var badgeWidth = 220;
  var badgeHeight = 52;
  var themeInputs = document.querySelectorAll('input[name="badge-theme"]');

  function selectedTheme() {
    var checked = document.querySelector('input[name="badge-theme"]:checked');
    return checked ? checked.value : 'dark';
  }

  function renderEmbedCode(theme) {
    document.querySelectorAll('.badge-embed-code').forEach(function(code) {
      var startupUrl = code.dataset.startupUrl;
      var badgeSrc = code.dataset.badgeSrc;
      var alt = code.dataset.alt;
      if (!startupUrl || !badgeSrc || !alt) return;
      var srcWithTheme = badgeSrc + '?theme=' + theme;
      var safeStartupUrl = startupUrl.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      var safeSrc = srcWithTheme.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      var safeAlt = alt.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      code.textContent = '<a href="' + safeStartupUrl + '" target="_blank"><img src="' + safeSrc + '" alt="' + safeAlt + '" width="' + badgeWidth + '" height="' + badgeHeight + '" style="border:0;"></a>';
    });
  }

  function renderPreviewBadges(theme) {
    document.querySelectorAll('.badge-preview-image').forEach(function(img) {
      var badgeSrc = img.dataset.badgeSrc;
      if (!badgeSrc) return;
      img.src = badgeSrc + '?theme=' + theme;
    });
  }

  function applyTheme(theme) {
    renderPreviewBadges(theme);
    renderEmbedCode(theme);
  }

  applyTheme(selectedTheme());

  themeInputs.forEach(function(input) {
    input.addEventListener('change', function() {
      applyTheme(selectedTheme());
    });
  });

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

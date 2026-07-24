<h1 class="dash-page-title">Badges</h1>
<div class="dash-welcome">
  Embed these badges on your site to show that your startup is listed on {{ $siteName }}. Copy the HTML and paste it where you want the badge to appear. Each badge links to your startup page.
</div>
<div class="badge-dofollow-note">All embed snippets below are generated as dofollow links.</div>
@php
  $defaultTheme = request('theme') === 'light' ? 'light' : 'dark';
@endphp

@if($startups->isEmpty())
<div class="dash-card badge-card">
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
<div class="dash-card badge-card">
  <div class="dash-card-header">
    <span class="dash-card-title">{{ $startup->name }}</span>
    <a href="{{ $startupUrl }}" target="_blank" rel="noopener" class="dash-table-link">View on {{ $siteName }}</a>
  </div>
  <div class="dash-card-body badge-card-body">
    {{-- Listed on Eden (always) --}}
    <div class="badge-row">
      <div class="badge-preview">
        <img
          src="{{ $badgeBaseUrl }}/listed?theme={{ $defaultTheme }}"
          alt="Listed on {{ $siteName }}"
          width="220"
          height="52"
          class="badge-preview-image"
          data-badge-src="{{ $badgeBaseUrl }}/listed">
        <span class="dash-badge dash-badge-success">Always available</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Listed on {{ $siteName }})</label>
      <pre class="dash-badge-code" role="button" tabindex="0" aria-label="Copy Listed on {{ $siteName }} embed code"><code class="badge-embed-code" data-startup-url="{{ $startupUrl }}" data-badge-src="{{ $badgeBaseUrl }}/listed" data-alt="Listed on {{ $siteName }}"></code></pre>
    </div>

    @if($isFeatured)
    <div class="badge-row">
      <div class="badge-preview">
        <img
          src="{{ $badgeBaseUrl }}/featured?theme={{ $defaultTheme }}"
          alt="Featured on {{ $siteName }}"
          width="220"
          height="52"
          class="badge-preview-image"
          data-badge-src="{{ $badgeBaseUrl }}/featured">
        <span class="dash-badge dash-badge-success">Featured</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Featured on {{ $siteName }})</label>
      <pre class="dash-badge-code" role="button" tabindex="0" aria-label="Copy Featured on {{ $siteName }} embed code"><code class="badge-embed-code" data-startup-url="{{ $startupUrl }}" data-badge-src="{{ $badgeBaseUrl }}/featured" data-alt="Featured on {{ $siteName }}"></code></pre>
    </div>
    @else
    <div class="badge-row badge-row--locked">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/featured?theme={{ $defaultTheme }}" alt="Featured on {{ $siteName }}" width="220" height="52" class="badge-preview-image badge-preview-image--unavailable" data-badge-src="{{ $badgeBaseUrl }}/featured">
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
          class="badge-preview-image"
          data-badge-src="{{ $badgeBaseUrl }}/product-of-day">
        <span class="dash-badge dash-badge-success">Product of the day</span>
      </div>
      <label class="dash-label badge-code-label">Embed code (Product of the day on {{ $siteName }})</label>
      <pre class="dash-badge-code" role="button" tabindex="0" aria-label="Copy Product of the day embed code"><code class="badge-embed-code" data-startup-url="{{ $startupUrl }}" data-badge-src="{{ $badgeBaseUrl }}/product-of-day" data-alt="Product of the day on {{ $siteName }}"></code></pre>
    </div>
    @else
    <div class="badge-row badge-row--locked">
      <div class="badge-preview">
        <img src="{{ $badgeBaseUrl }}/product-of-day?theme={{ $defaultTheme }}" alt="Product of the day on {{ $siteName }}" width="220" height="52" class="badge-preview-image badge-preview-image--unavailable" data-badge-src="{{ $badgeBaseUrl }}/product-of-day">
        <span class="dash-badge dash-badge-muted">Not product of the day</span>
      </div>
      <p class="badge-hint">This badge appears when your startup is product of the day (most upvotes that day).</p>
    </div>
    @endif
  </div>
</div>
@endforeach
@endif
<p id="badgeCopyFeedback" aria-live="polite" style="min-height:1.4em;color:var(--d-text-secondary);font-size:.875rem;">Select an embed code to copy it.</p>

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

  function copyEmbedCode(pre) {
      var code = pre.querySelector('code');
      if (!code) return;
      var feedback = document.getElementById('badgeCopyFeedback');
      var text = code.textContent;
      var copied = navigator.clipboard && window.isSecureContext
        ? navigator.clipboard.writeText(text)
        : Promise.reject(new Error('Clipboard unavailable'));
      copied.then(function() {
        pre.classList.add('is-copied');
        pre.setAttribute('aria-label', 'Copied embed code');
        if (feedback) feedback.textContent = 'Embed code copied to clipboard.';
        setTimeout(function() {
          pre.classList.remove('is-copied');
          pre.setAttribute('aria-label', 'Copy embed code');
        }, 1600);
      }).catch(function() {
        var range = document.createRange();
        range.selectNodeContents(code);
        var selection = window.getSelection();
        selection.removeAllRanges();
        selection.addRange(range);
        if (feedback) feedback.textContent = 'Embed code selected. Press Control C to copy.';
      });
  }

  document.querySelectorAll('.dash-badge-code').forEach(function(pre) {
    pre.title = 'Copy embed code';
    pre.addEventListener('click', function() {
      copyEmbedCode(pre);
    });
    pre.addEventListener('keydown', function(event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        copyEmbedCode(pre);
      }
    });
  });
})();
</script>

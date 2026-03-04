<h1 class="dash-page-title">Settings</h1>
<div class="dash-welcome">
  Application and cache settings.
</div>

<div class="dash-card">
  <div class="dash-card-header">
    <span class="dash-card-title">Cache</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 12px;">Clear the application cache if you changed config or need a fresh state.</p>
    <form action="{{ route('admin.cache.clear') }}" method="post" style="display: inline;">
      @csrf
      <button type="submit" class="dash-btn dash-btn-primary">
        <i class="fa-solid fa-broom"></i> Clear cache
      </button>
    </form>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">Google AdSense</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Enable Google AdSense and paste your AdSense script. When turned on, the script is embedded in the <code>&lt;head&gt;</code> of the whole site so Google can verify and serve ads.</p>
    <form action="{{ route('admin.settings.adsense') }}" method="post" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div style="display: flex; align-items: center; gap: 12px;">
          <input type="checkbox" id="adsense_enabled" name="adsense_enabled" value="1" {{ $adsenseEnabled ?? false ? 'checked' : '' }} class="dash-input" style="width: auto;">
          <label for="adsense_enabled" class="dash-label" style="margin-bottom: 0;">Turn on Google AdSense</label>
        </div>
        <div>
          <label for="adsense_script" class="dash-label">AdSense script</label>
          <textarea id="adsense_script" name="adsense_script" rows="6" class="dash-input" placeholder="Paste the script from your AdSense account (e.g. &lt;script async src=...&gt;&lt;/script&gt;)">{{ old('adsense_script', $adsenseScript ?? '') }}</textarea>
          @error('adsense_script') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 8px;">Paste the full script tag(s) from Google AdSense. This will be output in the site header when AdSense is on.</p>
        </div>
        <div>
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save AdSense settings</button>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">LinkedIn API</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Optional. When set, admins can feature founders on the hero section (Users list) and users can sign in with LinkedIn. Create an app at <a href="https://www.linkedin.com/developers/apps" target="_blank" rel="noopener">LinkedIn Developers</a>, add this callback URL to your app&rsquo;s Authorized redirect URLs, then paste the Client ID and Client Secret below.</p>
    <div style="margin-bottom: 16px;">
      <label class="dash-label">Callback URL</label>
      <div style="display: flex; align-items: center; gap: 8px; margin-top: 6px;">
        <input type="text" id="linkedin_callback_url" value="{{ url('/auth/linkedin/callback') }}" readonly class="dash-input" style="flex: 1; font-family: ui-monospace, monospace; font-size: 0.88rem;">
        <button type="button" class="dash-btn dash-btn-secondary" id="linkedin_copy_callback" title="Copy to clipboard">
          <i class="fa-regular fa-copy"></i> Copy
        </button>
      </div>
    </div>
    <form action="{{ route('admin.settings.linkedin') }}" method="post" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <label for="linkedin_client_id" class="dash-label">Client ID</label>
          <input type="text" id="linkedin_client_id" name="linkedin_client_id" class="dash-input" value="{{ old('linkedin_client_id', $linkedinClientId ?? '') }}" placeholder="Your LinkedIn app client ID" autocomplete="off">
          @error('linkedin_client_id') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="linkedin_client_secret" class="dash-label">Client Secret</label>
          <input type="password" id="linkedin_client_secret" name="linkedin_client_secret" class="dash-input" value="" placeholder="{{ $linkedinClientSecretSet ?? false ? 'Leave blank to keep current' : 'Your LinkedIn app client secret' }}" autocomplete="new-password">
          @error('linkedin_client_secret') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 8px;">When credentials are not set, the featured-founders hero block and &ldquo;Feature on hero&rdquo; button are hidden.</p>
        </div>
        <div>
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save LinkedIn credentials</button>
        </div>
      </div>
    </form>
    <script>
    (function() {
      var btn = document.getElementById('linkedin_copy_callback');
      var input = document.getElementById('linkedin_callback_url');
      if (btn && input) {
        btn.addEventListener('click', function() {
          input.select();
          input.setSelectionRange(0, 99999);
          try {
            navigator.clipboard.writeText(input.value);
          } catch (e) {
            document.execCommand('copy');
          }
          var orig = btn.innerHTML;
          btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
          setTimeout(function() { btn.innerHTML = orig; }, 2000);
        });
      }
    })();
    </script>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">Robots.txt</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Control what search engines crawl. The system recommends allowing public pages (home, about, categories, startups, sitemap) and disallowing backoffice, founder dashboard, login/register, and API. Edit below and save to update <code>public/robots.txt</code>.</p>
    <form action="{{ route('admin.settings.robots') }}" method="post" class="dash-form">
      @csrf
      <div style="display: flex; flex-direction: column; gap: 16px;">
        <div>
          <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 8px;">
            <label for="robots_txt" class="dash-label" style="margin-bottom: 0;">Content</label>
            <button type="button" id="robots_use_recommended" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Use recommended</button>
          </div>
          <textarea id="robots_txt" name="robots_txt" rows="18" class="dash-input" placeholder="User-agent: *&#10;Allow: /&#10;Disallow: /backoffice&#10;..." style="font-family: ui-monospace, monospace; font-size: 0.85rem;">{{ old('robots_txt', $robotsTxt ?? '') }}</textarea>
          @error('robots_txt') <span class="dash-error">{{ $message }}</span> @enderror
          <p class="dash-hint" style="margin-top: 8px;">Saved content is written to <code>public/robots.txt</code> and served at <a href="{{ url('/robots.txt') }}" target="_blank" rel="noopener">{{ url('/robots.txt') }}</a></p>
        </div>
        <div>
          <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save robots.txt</button>
        </div>
      </div>
    </form>
    <script>
    (function() {
      var btn = document.getElementById('robots_use_recommended');
      var ta = document.getElementById('robots_txt');
      var recommended = @json($recommendedRobotsTxt ?? '');
      if (btn && ta && recommended) {
        btn.addEventListener('click', function() {
          ta.value = recommended;
        });
      }
    })();
    </script>
  </div>
</div>

<div class="dash-card" style="margin-top: 20px;">
  <div class="dash-card-header">
    <span class="dash-card-title">Email</span>
  </div>
  <div class="dash-card-body">
    <p style="margin-bottom: 16px; color: #5f6368;">Configure SMTP or other email transport, the global HTML email wrapper, and the welcome / verification email templates used across Eden.</p>
    <a href="{{ route('admin.settings.email') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">
      <i class="fa-solid fa-envelope"></i> Open email settings
    </a>
  </div>
</div>


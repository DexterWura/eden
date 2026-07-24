@php
  $isEdit = $startup->exists;
  $formAction = $isEdit ? route('founder.startups.update', $startup) : route('founder.startups.store');
  $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

<h1 class="dash-page-title">{{ $isEdit ? 'Edit startup' : 'Add startup' }}</h1>
<div class="dash-welcome">
  {{ $isEdit ? 'Update your startup details and links.' : 'Add a new startup to your account.' }}
</div>

<section class="startup-backlink-offer {{ ($hasDofollowBacklink ?? false) ? 'startup-backlink-offer--active' : '' }}" aria-labelledby="backlink-offer-title">
  <div class="startup-backlink-offer__icon"><i class="fa-solid fa-link" aria-hidden="true"></i></div>
  <div class="startup-backlink-offer__copy">
    <h2 id="backlink-offer-title">{{ ($hasDofollowBacklink ?? false) ? 'Your Pro dofollow backlink is active' : 'Get a dofollow backlink with Pro' }}</h2>
    @if($hasDofollowBacklink ?? false)
      <p>Your startup website link can be followed by visitors and search engines while the listing owner remains a Pro member.</p>
    @else
      <p>Submitting is still free. Free listings use nofollow website links; Pro listings receive a dofollow website backlink.</p>
    @endif
  </div>
  @unless($hasDofollowBacklink ?? false)
    <a href="{{ route('pricing') }}" target="_blank" rel="noopener noreferrer" class="dash-btn dash-btn-primary"><i class="fa-solid fa-crown" aria-hidden="true"></i> View Pro</a>
  @endunless
</section>

<form action="{{ $formAction }}" method="post" class="dash-form startup-form" enctype="multipart/form-data">
  @csrf
  @if($isEdit) @method('PUT') @endif

  @include('eden.startups.partials.basics', ['isAdmin' => false])

  @include('eden.startups.partials.editorial', ['isAdmin' => false])

  <div class="dash-card founder-card-block" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Founders</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 20px;">
      <p style="font-size: 0.875rem; color: var(--d-text-secondary);">Add one or more founders. Each can have their own email and social links. Photo is optional.</p>
      <div id="founders-list">
        @php
          $foundersRaw = $startup->founders ?? [];
          $foundersList = [];
          if (old('founders_names')) {
            $names = old('founders_names', []);
            $emails = old('founders_emails', []);
            $twitters = old('founders_twitter_urls', []);
            $linkedins = old('founders_linkedin_urls', []);
            foreach ($names as $i => $n) {
              $foundersList[] = [
                'name' => $n ?? '',
                'email' => $emails[$i] ?? null,
                'twitter_url' => $twitters[$i] ?? null,
                'linkedin_url' => $linkedins[$i] ?? null,
              ];
            }
          } elseif (count($foundersRaw) > 0) {
            foreach ($foundersRaw as $f) {
              $foundersList[] = [
                'name' => is_array($f) ? ($f['name'] ?? '') : ($f->name ?? ''),
                'email' => is_array($f) ? ($f['email'] ?? null) : ($f->email ?? null),
                'twitter_url' => is_array($f) ? ($f['twitter_url'] ?? null) : ($f->twitter_url ?? null),
                'linkedin_url' => is_array($f) ? ($f['linkedin_url'] ?? null) : ($f->linkedin_url ?? null),
              ];
            }
          } else {
            $foundersList[] = [
              'name' => $startup->founder_name ?: (auth()->user()->name ?? ''),
              'email' => $startup->founder_email ?? (auth()->user()->email ?? null),
              'twitter_url' => $startup->founder_twitter_url ?? null,
              'linkedin_url' => $startup->founder_linkedin_url ?? null,
            ];
          }
        @endphp
        @foreach($foundersList as $idx => $fn)
        <div class="founder-row founder-card">
          <div class="founder-card-head">
            <span class="founder-card-num">Founder {{ $idx + 1 }}</span>
            <button type="button" class="dash-btn dash-btn-secondary founder-remove" aria-label="Remove founder"><i class="fa-solid fa-trash-can"></i> Remove</button>
          </div>
          <div class="founder-card-fields">
            <div class="founder-card-row">
              <div class="founder-field">
                <label class="dash-label">Name <span style="color: #dc2626;">*</span></label>
                <input type="text" name="founders_names[]" value="{{ $fn['name'] ?? '' }}" class="dash-input" placeholder="Full name" required>
              </div>
              <div class="founder-field founder-field-photo">
                <label class="dash-label">Photo</label>
                <input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
              </div>
            </div>
            <div class="founder-field">
              <label class="dash-label">Email</label>
              <input type="email" name="founders_emails[]" value="{{ $fn['email'] ?? '' }}" class="dash-input" placeholder="email@example.com">
            </div>
            <div class="founder-card-row founder-card-row-2">
              <div class="founder-field">
                <label class="dash-label">X (Twitter) handle</label>
                @php $twVal = old('founders_twitter_urls.'.$idx, $fn['twitter_url'] ?? ''); if ($twVal && preg_match('#(?:x\.com|twitter\.com)/([a-zA-Z0-9_]+)#i', $twVal, $m)) { $twVal = $m[1]; } @endphp
                <input type="text" name="founders_twitter_urls[]" value="{{ $twVal }}" class="dash-input" placeholder="e.g. @dxtwura">
              </div>
              <div class="founder-field">
                <label class="dash-label">LinkedIn profile</label>
                <input type="url" name="founders_linkedin_urls[]" value="{{ $fn['linkedin_url'] ?? '' }}" class="dash-input" placeholder="https://linkedin.com/in/...">
              </div>
            </div>
          </div>
        </div>
        @endforeach
        @if(count($foundersList) === 0)
        <div class="founder-row founder-card">
          <div class="founder-card-head">
            <span class="founder-card-num">Founder 1</span>
            <button type="button" class="dash-btn dash-btn-secondary founder-remove" aria-label="Remove founder"><i class="fa-solid fa-trash-can"></i> Remove</button>
          </div>
          <div class="founder-card-fields">
            <div class="founder-card-row">
              <div class="founder-field">
                <label class="dash-label">Name <span style="color: #dc2626;">*</span></label>
                <input type="text" name="founders_names[]" class="dash-input" placeholder="Full name" required>
              </div>
              <div class="founder-field founder-field-photo">
                <label class="dash-label">Photo</label>
                <input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
              </div>
            </div>
            <div class="founder-field">
              <label class="dash-label">Email</label>
              <input type="email" name="founders_emails[]" class="dash-input" placeholder="email@example.com">
            </div>
            <div class="founder-card-row founder-card-row-2">
              <div class="founder-field">
                <label class="dash-label">X (Twitter) handle</label>
                <input type="text" name="founders_twitter_urls[]" class="dash-input" placeholder="e.g. @dxtwura">
              </div>
              <div class="founder-field">
                <label class="dash-label">LinkedIn profile</label>
                <input type="url" name="founders_linkedin_urls[]" class="dash-input" placeholder="https://linkedin.com/in/...">
              </div>
            </div>
          </div>
        </div>
        @endif
      </div>
      <button type="button" id="founder-add" class="dash-btn dash-btn-secondary"><i class="fa-solid fa-plus"></i> Add founder</button>
    </div>
  </div>

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Startup links</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <div>
        <label for="website" class="dash-label">Website</label>
        <input type="url" id="website" name="website" value="{{ old('website', $startup->website) }}" class="dash-input" placeholder="https://...">
        @error('website') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label for="twitter_url" class="dash-label">Startup X (Twitter) handle</label>
          @php $startupTw = old('twitter_url', $startup->twitter_url); if ($startupTw && preg_match('#(?:x\.com|twitter\.com)/([a-zA-Z0-9_]+)#i', $startupTw, $m)) { $startupTw = $m[1]; } @endphp
          <input type="text" id="twitter_url" name="twitter_url" value="{{ $startupTw }}" class="dash-input" placeholder="e.g. @dxtwura">
          @error('twitter_url') <span class="dash-error">{{ $message }}</span> @enderror
          <span class="dash-hint" style="display: block; margin-top: 4px; font-size: 0.8rem; color: var(--d-text-secondary);">We'll add x.com/ for you.</span>
        </div>
        <div>
          <label for="linkedin_url" class="dash-label">Startup LinkedIn</label>
          <input type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $startup->linkedin_url) }}" class="dash-input" placeholder="https://linkedin.com/company/...">
          @error('linkedin_url') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
      </div>
      <div style="margin-top: 8px; padding-top: 12px; border-top: 1px solid var(--d-border, #2a2e3d);">
        <label for="search_console_property" class="dash-label">Google Search Console property</label>
        <input
          type="text"
          id="search_console_property"
          name="search_console_property"
          value="{{ old('search_console_property', $startup->search_console_property) }}"
          class="dash-input"
          placeholder="e.g. https://example.com/ or sc-domain:example.com"
        >
        @error('search_console_property') <span class="dash-error">{{ $message }}</span> @enderror
        <p class="dash-hint" style="margin-top: 4px; font-size: 0.8rem; color: var(--d-text-secondary);">
          Enter the exact Search Console property for this startup. Eden will validate it using the configured Google Search Console API key.
        </p>
      </div>
    </div>
  </div>

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Revenue &amp; metrics</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <p style="font-size: 0.875rem; color: var(--d-text-secondary);">Optional. Used for leaderboard sorting and display. Views and clicks are tracked when visitors view your page or click your website link.</p>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label for="mrr" class="dash-label">MRR (Monthly Recurring Revenue)</label>
          <input type="number" id="mrr" name="mrr" value="{{ old('mrr', $startup->mrr) }}" class="dash-input" placeholder="0" min="0" step="0.01">
          @error('mrr') <span class="dash-error">{{ $message }}</span> @enderror
          <span class="dash-hint" style="display: block; margin-top: 4px; font-size: 0.8rem; color: var(--d-text-secondary);">Numeric value, any currency.</span>
        </div>
        <div>
          <label for="revenue" class="dash-label">Revenue (total or annual)</label>
          <input type="number" id="revenue" name="revenue" value="{{ old('revenue', $startup->revenue) }}" class="dash-input" placeholder="0" min="0" step="0.01">
          @error('revenue') <span class="dash-error">{{ $message }}</span> @enderror
          <span class="dash-hint" style="display: block; margin-top: 4px; font-size: 0.8rem; color: var(--d-text-secondary);">Numeric value, any currency.</span>
        </div>
      </div>
      @if($isEdit && (isset($startup->views) || isset($startup->clicks)))
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; font-size: 0.875rem; color: var(--d-text-secondary);">
        <div>Views: <strong>{{ (int)($startup->views ?? 0) }}</strong></div>
        <div>Clicks: <strong>{{ (int)($startup->clicks ?? 0) }}</strong></div>
      </div>
      @endif
      <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--d-border, #2a2e3d);">
        <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
          <input type="hidden" name="traffic_tracking_enabled" value="0">
          <input type="checkbox" name="traffic_tracking_enabled" value="1" id="traffic_tracking_enabled" {{ old('traffic_tracking_enabled', $startup->traffic_tracking_enabled) ? 'checked' : '' }}>
          <span class="dash-label">Enable website traffic tracking</span>
        </label>
        <p class="dash-hint" style="margin: 8px 0 0; font-size: 0.8rem; color: var(--d-text-secondary);">Add a script to your site to track visits. Stats are shown on your startup page.</p>
        @if($isEdit && $startup->traffic_tracking_enabled)
        @php $trafficScript = '<script async src="' . url('/api/eden/v1/track.js') . '?slug=' . e($startup->slug) . '"></script>'; @endphp
        <div id="traffic-script-snippet" style="margin-top: 12px; padding: 12px; background: var(--d-surface); border: 1px solid var(--d-border); border-radius: var(--d-radius);">
          <p style="font-size: 0.8rem; color: var(--d-text-secondary); margin: 0 0 8px;">Add this to your site&rsquo;s <code>&lt;head&gt;</code>:</p>
          <pre style="margin: 0; padding: 10px; font-size: 0.75rem; overflow-x: auto; background: var(--d-bg); border-radius: 4px;"><code>{{ e($trafficScript) }}</code></pre>
          <button type="button" class="dash-btn dash-btn-secondary" style="margin-top: 8px; font-size: 0.8rem;" onclick="navigator.clipboard.writeText({{ json_encode($trafficScript) }}); this.textContent='Copied!'; setTimeout(function(){this.textContent='Copy script';}.bind(this), 2000);">Copy script</button>
        </div>
        @endif
      </div>
    </div>
  </div>

  @if($isEdit && auth()->user()->isPro())
  @php $fundingRound = $startup->activeFundingRound; @endphp
  <div class="dash-card" style="margin-bottom:20px;border-left:4px solid #6366f1;">
    <div class="dash-card-header">
      <span class="dash-card-title"><i class="fa-solid fa-hand-holding-dollar"></i> Fundraising</span>
      <span class="dash-card-subtitle">{{ $fundingRound ? $fundingRound->round_type_label . ' · live' : 'No active round' }}</span>
    </div>
    <div class="dash-card-body">
      <p style="font-size:.875rem;color:var(--d-text-secondary);">Fundraising details are managed in one place so your startup profile and investor listing stay consistent.</p>
      <a href="{{ route('founder.fundraising.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration:none;">Manage fundraising</a>
    </div>
  </div>
  @elseif($isEdit)
  <div class="dash-card" style="margin-bottom: 20px; border-left: 4px solid #f59e0b;">
    <div class="dash-card-header">
      <span class="dash-card-title"><i class="fa-solid fa-hand-holding-dollar"></i> Funding / Investors</span>
      <span class="dash-card-subtitle">Pro only</span>
    </div>
    <div class="dash-card-body" style="display:flex; flex-direction:column; gap:14px;">
      <p style="font-size: 0.875rem; color: var(--d-text-secondary); margin:0;">Show that you're raising funding, publish investor-facing round details, and manage it from your dashboard.</p>
      <div>
        <a href="{{ url('/pricing') }}" class="dash-btn dash-btn-primary" style="text-decoration:none;"><i class="fa-solid fa-crown"></i> Upgrade to Pro</a>
      </div>
    </div>
  </div>
  @endif

  <div class="founder-card-block" style="margin-top: 24px;">
    <div class="founder-card">
      <div class="founder-card-head">
        <span class="founder-card-num">List for sale</span>
      </div>
      <p style="font-size: 0.875rem; color: var(--d-text-secondary); margin-bottom: 16px;">Link this startup to a FLIPit listing. When the listing sells on FLIPit, Eden will automatically remove the "For sale" badge.</p>
      <div class="dash-form" style="display: flex; flex-direction: column; gap: 14px;">
        <div>
          <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
            <input type="hidden" name="for_sale" value="0">
            <input type="checkbox" name="for_sale" value="1" {{ old('for_sale', $startup->for_sale) ? 'checked' : '' }}>
            <span class="dash-label">Mark this startup as for sale</span>
          </label>
        </div>
        <div>
          <label for="flipit_listing_url" class="dash-label">FLIPit listing number</label>
          @php
            $flipitDisplay = old('flipit_listing_url');
            if ($flipitDisplay === null && $startup->flipit_listing_id) {
              $flipitDisplay = \App\Models\Startup::isFlipitListingNumber($startup->flipit_listing_id)
                ? $startup->flipit_listing_id
                : 'https://flipit.co.zw/marketplace/listing/'.$startup->flipit_listing_id;
            }
          @endphp
          <input type="text" id="flipit_listing_url" name="flipit_listing_url" value="{{ $flipitDisplay }}" class="dash-input" placeholder="e.g. AB12CD34EF56">
          @error('flipit_listing_url') <span class="dash-error">{{ $message }}</span> @enderror
          <span class="dash-hint" style="display: block; margin-top: 4px; font-size: 0.8rem; color: var(--d-text-secondary);">Find this in your FLIPit dashboard under My Listings. You can paste the listing number (e.g. AB12CD34EF56) or the full listing URL.</span>
        </div>
      </div>
    </div>
  </div>

  @if(!$isEdit)
  <div style="background:var(--surface-hover,#1a1d28);border:1px solid var(--border,#2a2e3d);border-left:4px solid var(--accent,#00d4aa);border-radius:8px;padding:14px 18px;margin-bottom:16px;font-size:0.92rem;color:var(--text-muted,#8b90a0)">
    <i class="fa-solid fa-info-circle" style="color:var(--accent,#00d4aa);margin-right:6px"></i>
    Your startup will be reviewed by our team before going live. This usually takes less than 24 hours.
  </div>
  @endif
  <div style="display: flex; gap: 12px; flex-wrap: wrap;">
    <button type="submit" class="dash-btn dash-btn-primary">
      <i class="fa-solid fa-check"></i> {{ $isEdit ? 'Save changes' : 'Submit startup' }}
    </button>
    <a href="{{ route('founder.startups.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
  </div>
</form>

<script src="{{ asset('js/startup-form.js') }}"></script>

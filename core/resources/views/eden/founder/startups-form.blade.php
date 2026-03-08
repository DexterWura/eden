@php
  $isEdit = $startup->exists;
  $formAction = $isEdit ? route('founder.startups.update', $startup) : route('founder.startups.store');
  $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

<h1 class="dash-page-title">{{ $isEdit ? 'Edit startup' : 'Add startup' }}</h1>
<div class="dash-welcome">
  {{ $isEdit ? 'Update your startup details and links.' : 'Add a new startup to your account.' }}
</div>

<form action="{{ $formAction }}" method="post" class="dash-form" enctype="multipart/form-data">
  @csrf
  @if($isEdit) @method('PUT') @endif

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Basics</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <div>
        <label for="name" class="dash-label">Startup name <span style="color: #dc2626;">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $startup->name) }}" required class="dash-input" placeholder="e.g. Nexus Pay">
        @error('name') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="tagline" class="dash-label">Tagline</label>
        <input type="text" id="tagline" name="tagline" value="{{ old('tagline', $startup->tagline) }}" class="dash-input" placeholder="Short one-liner">
        @error('tagline') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="description" class="dash-label">Description</label>
        <textarea id="description" name="description" rows="4" class="dash-input" placeholder="What does the startup do?">{{ old('description', $startup->description) }}</textarea>
        @error('description') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label for="category" class="dash-label">Category</label>
          <select id="category" name="category" class="dash-input">
            <option value="">Choose a category…</option>
            @foreach($categories ?? [] as $cat)
            <option value="{{ $cat->name }}" {{ old('category', $startup->category) === $cat->name ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
          </select>
          @error('category') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="location" class="dash-label">Location</label>
          <input type="text" id="location" name="location" value="{{ old('location', $startup->location) }}" class="dash-input" placeholder="e.g. Harare">
          @error('location') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
      </div>
      <div>
        <label for="launch_date" class="dash-label">Launch date</label>
        <input type="date" id="launch_date" name="launch_date" value="{{ old('launch_date', $startup->launch_date ? $startup->launch_date->format('Y-m-d') : '') }}" class="dash-input">
        @error('launch_date') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="logo_path" class="dash-label">Startup logo</label>
        <p class="dash-hint" style="margin-top: 0;">80×80 px or smaller, square.</p>
        <input type="file" id="logo_path" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
        @error('logo') <span class="dash-error">{{ $message }}</span> @enderror
        @if($startup->logo_path)
          <p style="margin-top: 8px;"><img src="{{ asset($startup->logo_path) }}" alt="Current logo" style="max-width: 80px; height: auto; border-radius: 8px;"> Current logo. Upload to replace.</p>
        @endif
      </div>
      <div>
        <label class="dash-label">Product images</label>
        <p class="dash-hint" style="margin-bottom: 8px;">You can add more than one image. Select multiple files at once or use "Add more images" to attach another set.</p>
        <div id="product-images-container">
          <input type="file" name="product_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple class="dash-input product-images-input">
        </div>
        <button type="button" id="add-more-product-images" class="dash-btn dash-btn-secondary" style="margin-top: 10px; font-size: 0.875rem;"><i class="fa-solid fa-plus"></i> Add more images</button>
        <p id="product-images-summary" class="dash-hint" style="margin-top: 8px; display: none;"></p>
        @error('product_images') <span class="dash-error">{{ $message }}</span> @enderror
        @if(!empty($startup->product_images))
          <p style="margin-top: 8px; font-size: 0.875rem; color: var(--d-text-secondary);">{{ count($startup->product_images) }} image(s) currently. Upload above to add or replace.</p>
        @endif
      </div>
    </div>
  </div>

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
  @php
    $fundingRound = $startup->activeFundingRound;
  @endphp
  <div class="dash-card" style="margin-bottom: 20px; border-left: 4px solid #6366f1;">
    <div class="dash-card-header">
      <span class="dash-card-title"><i class="fa-solid fa-hand-holding-dollar"></i> Funding / Investors</span>
      <span class="dash-card-subtitle">Pro feature</span>
    </div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <p style="font-size: 0.875rem; color: var(--d-text-secondary);">Open a funding round or mark that you're looking for investors. This will be shown on your startup page.</p>
      <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
        <input type="hidden" name="seeking_investors" value="0">
        <input type="checkbox" name="seeking_investors" value="1" id="seeking_investors" {{ (old('seeking_investors', $fundingRound ? '1' : '0')) === '1' ? 'checked' : '' }}>
        <span class="dash-label">We are raising funding / looking for investors</span>
      </label>
      @php
        $fundingRoundType = $fundingRound ? $fundingRound->round_type : 'seed';
        $fundingAmount = $fundingRound ? $fundingRound->amount_seeking : '';
        $fundingCurrency = $fundingRound ? $fundingRound->currency : 'USD';
        $fundingContact = $fundingRound ? $fundingRound->contact_email : ($startup->founder_email ?? '');
        $fundingDesc = $fundingRound ? $fundingRound->description : '';
      @endphp
      <div id="funding-round-fields" style="{{ (old('seeking_investors', $fundingRound ? '1' : '0')) === '1' ? '' : 'display:none;' }}">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
            <label for="funding_round_type" class="dash-label">Round type</label>
            <select id="funding_round_type" name="funding_round_type" class="dash-input">
              @foreach($fundingRoundTypes ?? [] as $val => $label)
              <option value="{{ $val }}" {{ old('funding_round_type', $fundingRoundType) === $val ? 'selected' : '' }}>{{ $label }}</option>
              @endforeach
            </select>
          </div>
          <div>
            <label for="funding_amount_seeking" class="dash-label">Amount seeking (optional)</label>
            <input type="number" id="funding_amount_seeking" name="funding_amount_seeking" value="{{ old('funding_amount_seeking', $fundingAmount) }}" class="dash-input" placeholder="e.g. 500000" min="0" step="0.01">
          </div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
          <div>
            <label for="funding_currency" class="dash-label">Currency</label>
            <input type="text" id="funding_currency" name="funding_currency" value="{{ old('funding_currency', $fundingCurrency) }}" class="dash-input" placeholder="USD" maxlength="3">
          </div>
          <div>
            <label for="funding_contact_email" class="dash-label">Contact email for investors</label>
            <input type="email" id="funding_contact_email" name="funding_contact_email" value="{{ old('funding_contact_email', $fundingContact) }}" class="dash-input" placeholder="investors@example.com">
          </div>
        </div>
        <div>
          <label for="funding_description" class="dash-label">Description (optional)</label>
          <textarea id="funding_description" name="funding_description" rows="3" class="dash-input" placeholder="Brief pitch, use of funds, etc.">{{ old('funding_description', $fundingDesc) }}</textarea>
        </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
  function updateFounderNumbers() {
    document.querySelectorAll('#founders-list .founder-card-num').forEach(function(el, i) { el.textContent = 'Founder ' + (i + 1); });
  }
  document.getElementById('founder-add').addEventListener('click', function() {
    var t = document.getElementById('founders-list');
    var n = t.querySelectorAll('.founder-row').length + 1;
    var row = document.createElement('div');
    row.className = 'founder-row founder-card';
    row.innerHTML = '<div class="founder-card-head"><span class="founder-card-num">Founder ' + n + '</span><button type="button" class="dash-btn dash-btn-secondary founder-remove" aria-label="Remove founder"><i class="fa-solid fa-trash-can"></i> Remove</button></div><div class="founder-card-fields"><div class="founder-card-row"><div class="founder-field"><label class="dash-label">Name <span style="color:#dc2626">*</span></label><input type="text" name="founders_names[]" class="dash-input" placeholder="Full name" required></div><div class="founder-field founder-field-photo"><label class="dash-label">Photo</label><input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input"></div></div><div class="founder-field"><label class="dash-label">Email</label><input type="email" name="founders_emails[]" class="dash-input" placeholder="email@example.com"></div><div class="founder-card-row founder-card-row-2"><div class="founder-field"><label class="dash-label">X (Twitter) handle</label><input type="text" name="founders_twitter_urls[]" class="dash-input" placeholder="e.g. @dxtwura"></div><div class="founder-field"><label class="dash-label">LinkedIn profile</label><input type="url" name="founders_linkedin_urls[]" class="dash-input" placeholder="https://linkedin.com/in/..."></div></div></div></div>';
    t.appendChild(row);
    row.querySelector('.founder-remove').addEventListener('click', function() { row.remove(); updateFounderNumbers(); });
    updateFounderNumbers();
  });
  var seekingCheck = document.getElementById('seeking_investors');
  var fundingFields = document.getElementById('funding-round-fields');
  if (seekingCheck && fundingFields) {
    seekingCheck.addEventListener('change', function() { fundingFields.style.display = seekingCheck.checked ? '' : 'none'; });
  }
  document.querySelectorAll('#founders-list .founder-remove').forEach(function(btn) {
    btn.addEventListener('click', function() { btn.closest('.founder-row').remove(); updateFounderNumbers(); });
  });

  var container = document.getElementById('product-images-container');
  var addImgBtn = document.getElementById('add-more-product-images');
  var summaryEl = document.getElementById('product-images-summary');
  if (container && addImgBtn) {
    function countProductFiles() {
      var inputs = container.querySelectorAll('input[name="product_images[]"]');
      var total = 0;
      inputs.forEach(function(inp) { total += (inp.files && inp.files.length) ? inp.files.length : 0; });
      return total;
    }
    function updateProductSummary() {
      var n = countProductFiles();
      if (summaryEl) { summaryEl.style.display = n ? 'block' : 'none'; summaryEl.textContent = n === 1 ? '1 image selected' : n + ' images selected'; }
    }
    addImgBtn.addEventListener('click', function() {
      var input = document.createElement('input');
      input.type = 'file'; input.name = 'product_images[]'; input.accept = 'image/jpeg,image/png,image/gif,image/webp';
      input.multiple = true; input.className = 'dash-input product-images-input'; input.style.marginTop = '10px';
      input.addEventListener('change', updateProductSummary);
      container.appendChild(input);
    });
    container.addEventListener('change', function(e) { if (e.target.name === 'product_images[]') updateProductSummary(); });
  }
});
</script>
<style>
.dash-form .dash-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--d-text); }
.dash-form .dash-input { width: 100%; padding: 10px 14px; font-size: 0.875rem; border: 1px solid var(--d-border); border-radius: var(--d-radius); background: var(--d-surface); color: var(--d-text); }
.dash-form .dash-input:focus { outline: none; border-color: var(--d-primary); }
.dash-form .dash-error { display: block; margin-top: 4px; font-size: 0.8rem; color: #dc2626; }
.founder-card-block .founder-card { background: var(--d-surface, #1a1d28); border: 1px solid var(--d-border, #2a2e3d); border-radius: var(--d-radius, 12px); padding: 20px; }
.founder-card-block .founder-card-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--d-border, #2a2e3d); }
.founder-card-block .founder-card-num { font-weight: 600; font-size: 0.9rem; color: var(--d-text-secondary, #8b90a0); }
.founder-card-block .founder-card-fields { display: flex; flex-direction: column; gap: 14px; }
.founder-card-block .founder-card-row { display: grid; grid-template-columns: 1fr 180px; gap: 16px; align-items: start; }
.founder-card-block .founder-card-row-2 { grid-template-columns: 1fr 1fr; }
.founder-card-block .founder-field-photo { min-width: 0; }
@media (max-width: 640px) {
  .dash-form [style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; }
  .founder-card-block .founder-card-row { grid-template-columns: 1fr !important; }
  .founder-card-block .founder-card-row-2 { grid-template-columns: 1fr !important; }
}
</style>

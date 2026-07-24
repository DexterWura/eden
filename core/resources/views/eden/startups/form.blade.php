@php
  $isEdit = $startup->exists;
  $formAction = $isEdit ? route('admin.startups.update', $startup) : route('admin.startups.store');
  $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

<h1 class="dash-page-title">{{ $isEdit ? 'Edit startup' : 'Add startup' }}</h1>
<div class="dash-welcome">
  {{ $isEdit ? 'Update startup details, founder, links and status.' : 'Create a new startup and set the founder, socials and details.' }}
</div>

<form action="{{ $formAction }}" method="post" class="dash-form startup-form" enctype="multipart/form-data">
  @csrf
  @if($isEdit) @method('PUT') @endif

  @include('eden.startups.partials.basics', ['isAdmin' => true])

  @include('eden.startups.partials.editorial', ['isAdmin' => true])

  <div class="dash-card founder-card-block" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Founders</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 20px;">
      <p style="font-size: 0.875rem; color: var(--d-text-secondary);">Each founder can have their own email and social links.</p>
      <div id="founders-list">
        @php
          $adminFoundersRaw = $startup->founders ?? [];
          $adminFoundersList = [];
          if (old('founders_names')) {
            $names = old('founders_names', []);
            $emails = old('founders_emails', []);
            $twitters = old('founders_twitter_urls', []);
            $linkedins = old('founders_linkedin_urls', []);
            foreach ($names as $i => $n) {
              $adminFoundersList[] = [
                'name' => $n ?? '',
                'email' => $emails[$i] ?? null,
                'twitter_url' => $twitters[$i] ?? null,
                'linkedin_url' => $linkedins[$i] ?? null,
              ];
            }
          } elseif (count($adminFoundersRaw) > 0) {
            foreach ($adminFoundersRaw as $f) {
              $adminFoundersList[] = [
                'name' => is_array($f) ? ($f['name'] ?? '') : ($f->name ?? ''),
                'email' => is_array($f) ? ($f['email'] ?? null) : ($f->email ?? null),
                'twitter_url' => is_array($f) ? ($f['twitter_url'] ?? null) : ($f->twitter_url ?? null),
                'linkedin_url' => is_array($f) ? ($f['linkedin_url'] ?? null) : ($f->linkedin_url ?? null),
              ];
            }
          } else {
            $adminFoundersList[] = [
              'name' => $startup->founder_name ?? '',
              'email' => $startup->founder_email ?? null,
              'twitter_url' => $startup->founder_twitter_url ?? null,
              'linkedin_url' => $startup->founder_linkedin_url ?? null,
            ];
          }
        @endphp
        @foreach($adminFoundersList as $idx => $fn)
        <div class="founder-row founder-card">
          <div class="founder-card-head">
            <span class="founder-card-num">Founder {{ $idx + 1 }}</span>
            <button type="button" class="dash-btn dash-btn-secondary founder-remove" aria-label="Remove founder"><i class="fa-solid fa-trash-can"></i> Remove</button>
          </div>
          <div class="founder-card-fields">
            <div class="founder-card-row">
              <div class="founder-field">
                <label class="dash-label">Name</label>
                <input type="text" name="founders_names[]" value="{{ $fn['name'] ?? '' }}" class="dash-input" placeholder="Full name">
              </div>
              <div class="founder-field founder-field-photo">
                <label class="dash-label">Photo upload</label>
                <input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
                @if(!empty($fn['photo_url']))
                <div style="margin-top:4px;display:flex;align-items:center;gap:6px">
                  <img src="{{ asset($fn['photo_url']) }}" style="width:28px;height:28px;border-radius:50%;object-fit:cover" alt="">
                  <span style="font-size:0.75rem;color:var(--text-muted)">Current photo</span>
                </div>
                @endif
              </div>
            </div>
            <div class="founder-field">
              <label class="dash-label">Photo URL <span style="font-weight:normal;color:var(--text-muted)">(or paste an image link)</span></label>
              <input type="url" name="founders_photo_urls[]" value="{{ $fn['photo_url'] ?? '' }}" class="dash-input" placeholder="https://example.com/photo.jpg">
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
        @if(count($adminFoundersList) === 0)
        <div class="founder-row founder-card">
          <div class="founder-card-head">
            <span class="founder-card-num">Founder 1</span>
            <button type="button" class="dash-btn dash-btn-secondary founder-remove" aria-label="Remove founder"><i class="fa-solid fa-trash-can"></i> Remove</button>
          </div>
          <div class="founder-card-fields">
            <div class="founder-card-row">
              <div class="founder-field">
                <label class="dash-label">Name</label>
                <input type="text" name="founders_names[]" class="dash-input" placeholder="Full name">
              </div>
              <div class="founder-field founder-field-photo">
                <label class="dash-label">Photo upload</label>
                <input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
              </div>
            </div>
            <div class="founder-field">
              <label class="dash-label">Photo URL <span style="font-weight:normal;color:var(--text-muted)">(or paste an image link)</span></label>
              <input type="url" name="founders_photo_urls[]" value="" class="dash-input" placeholder="https://example.com/photo.jpg">
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
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label for="mrr" class="dash-label">MRR (Monthly Recurring Revenue)</label>
          <input type="number" id="mrr" name="mrr" value="{{ old('mrr', $startup->mrr) }}" class="dash-input" placeholder="0" min="0" step="0.01">
          @error('mrr') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="revenue" class="dash-label">Revenue (total or annual)</label>
          <input type="number" id="revenue" name="revenue" value="{{ old('revenue', $startup->revenue) }}" class="dash-input" placeholder="0" min="0" step="0.01">
          @error('revenue') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
      </div>
      @if($isEdit)
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label for="views" class="dash-label">Views (tracked when startup page is viewed)</label>
          <input type="number" id="views" name="views" value="{{ old('views', $startup->views ?? 0) }}" class="dash-input" min="0">
          @error('views') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="clicks" class="dash-label">Clicks (tracked when Visit website is clicked)</label>
          <input type="number" id="clicks" name="clicks" value="{{ old('clicks', $startup->clicks ?? 0) }}" class="dash-input" min="0">
          @error('clicks') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
      </div>
      @endif
    </div>
  </div>

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Status & visibility</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      @if($isEdit)
      <div>
        <label for="status" class="dash-label">Status</label>
        <select id="status" name="status" class="dash-input">
          <option value="active" {{ old('status', $startup->status) === 'active' ? 'selected' : '' }}>Active</option>
          <option value="disabled" {{ old('status', $startup->status) === 'disabled' ? 'selected' : '' }}>Disabled</option>
          <option value="dormant" {{ old('status', $startup->status) === 'dormant' ? 'selected' : '' }}>Dormant</option>
          <option value="banned" {{ old('status', $startup->status) === 'banned' ? 'selected' : '' }}>Banned</option>
        </select>
        @error('status') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      @endif
      <div style="display: flex; align-items: center; gap: 10px;">
        <input type="hidden" name="is_featured" value="0">
        <input type="checkbox" id="is_featured" name="is_featured" value="1" {{ old('is_featured', $startup->is_featured) ? 'checked' : '' }} style="width: 18px; height: 18px;">
        <label for="is_featured" class="dash-label" style="margin: 0;">Featured on homepage</label>
      </div>
    </div>
  </div>

  <div style="display: flex; gap: 12px; flex-wrap: wrap;">
    <button type="submit" class="dash-btn dash-btn-primary">
      <i class="fa-solid fa-check"></i> {{ $isEdit ? 'Save changes' : 'Create startup' }}
    </button>
    <a href="{{ route('admin.startups.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
  </div>
</form>

<script src="{{ asset('js/startup-form.js') }}"></script>

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
          <input type="text" id="category" name="category" value="{{ old('category', $startup->category) }}" class="dash-input" placeholder="e.g. Fintech">
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
        <input type="file" id="logo_path" name="logo" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
        @error('logo') <span class="dash-error">{{ $message }}</span> @enderror
        @if($startup->logo_path)
          <p style="margin-top: 8px;"><img src="{{ asset($startup->logo_path) }}" alt="Current logo" style="max-width: 80px; height: auto; border-radius: 8px;"> Current logo. Upload to replace.</p>
        @endif
      </div>
      <div>
        <label class="dash-label">Product images</label>
        <input type="file" name="product_images[]" accept="image/jpeg,image/png,image/gif,image/webp" multiple class="dash-input">
        @error('product_images') <span class="dash-error">{{ $message }}</span> @enderror
        @if(!empty($startup->product_images))
          <p style="margin-top: 8px; font-size: 0.875rem; color: var(--d-text-secondary);">{{ count($startup->product_images) }} image(s). Upload more to add.</p>
        @endif
      </div>
    </div>
  </div>

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Founders</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <p style="font-size: 0.875rem; color: var(--d-text-secondary);">Add one or more founders. Profile photo is optional (round avatar with initials used if not set).</p>
      <div id="founders-list">
        @php $foundersList = old('founders_names', $startup->founders_display ? array_column($startup->founders_display, 'name') : ($startup->founder_name ? [$startup->founder_name] : [auth()->user()->name ?? ''])); @endphp
        @foreach($foundersList as $idx => $fn)
        <div class="founder-row" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 140px;">
            <label class="dash-label">Name</label>
            <input type="text" name="founders_names[]" value="{{ is_string($fn) ? $fn : ($fn['name'] ?? '') }}" class="dash-input" placeholder="Full name">
          </div>
          <div style="min-width: 160px;">
            <label class="dash-label">Photo (optional)</label>
            <input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
          </div>
          <button type="button" class="dash-btn dash-btn-secondary founder-remove" style="flex-shrink: 0;">Remove</button>
        </div>
        @endforeach
        @if(count($foundersList) === 0)
        <div class="founder-row" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
          <div style="flex: 1; min-width: 140px;">
            <label class="dash-label">Name</label>
            <input type="text" name="founders_names[]" class="dash-input" placeholder="Full name">
          </div>
          <div style="min-width: 160px;">
            <label class="dash-label">Photo (optional)</label>
            <input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input">
          </div>
          <button type="button" class="dash-btn dash-btn-secondary founder-remove" style="flex-shrink: 0;">Remove</button>
        </div>
        @endif
      </div>
      <button type="button" id="founder-add" class="dash-btn dash-btn-secondary" style="align-self: flex-start;"><i class="fa-solid fa-plus"></i> Add founder</button>
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label for="founder_email" class="dash-label">Contact email</label>
          <input type="email" id="founder_email" name="founder_email" value="{{ old('founder_email', $startup->founder_email ?? auth()->user()->email) }}" class="dash-input" placeholder="email@example.com">
          @error('founder_email') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div></div>
      </div>
      <div>
        <label for="founder_twitter_url" class="dash-label">Your Twitter / X</label>
        <input type="url" id="founder_twitter_url" name="founder_twitter_url" value="{{ old('founder_twitter_url', $startup->founder_twitter_url) }}" class="dash-input" placeholder="https://twitter.com/...">
        @error('founder_twitter_url') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="founder_linkedin_url" class="dash-label">Your LinkedIn</label>
        <input type="url" id="founder_linkedin_url" name="founder_linkedin_url" value="{{ old('founder_linkedin_url', $startup->founder_linkedin_url) }}" class="dash-input" placeholder="https://linkedin.com/in/...">
        @error('founder_linkedin_url') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
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
          <label for="twitter_url" class="dash-label">Startup Twitter / X</label>
          <input type="url" id="twitter_url" name="twitter_url" value="{{ old('twitter_url', $startup->twitter_url) }}" class="dash-input" placeholder="https://twitter.com/...">
          @error('twitter_url') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="linkedin_url" class="dash-label">Startup LinkedIn</label>
          <input type="url" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $startup->linkedin_url) }}" class="dash-input" placeholder="https://linkedin.com/company/...">
          @error('linkedin_url') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
      </div>
    </div>
  </div>

  <div style="display: flex; gap: 12px; flex-wrap: wrap;">
    <button type="submit" class="dash-btn dash-btn-primary">
      <i class="fa-solid fa-check"></i> {{ $isEdit ? 'Save changes' : 'Add startup' }}
    </button>
    <a href="{{ route('founder.startups.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration: none;">Cancel</a>
  </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
  document.getElementById('founder-add').addEventListener('click', function() {
    var t = document.getElementById('founders-list');
    var row = document.createElement('div');
    row.className = 'founder-row';
    row.style.cssText = 'display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;';
    row.innerHTML = '<div style="flex: 1; min-width: 140px;"><label class="dash-label">Name</label><input type="text" name="founders_names[]" class="dash-input" placeholder="Full name"></div><div style="min-width: 160px;"><label class="dash-label">Photo (optional)</label><input type="file" name="founders_photos[]" accept="image/jpeg,image/png,image/gif,image/webp" class="dash-input"></div><button type="button" class="dash-btn dash-btn-secondary founder-remove" style="flex-shrink: 0;">Remove</button>';
    t.appendChild(row);
    row.querySelector('.founder-remove').addEventListener('click', function() { row.remove(); });
  });
  document.getElementById('founders-list').querySelectorAll('.founder-remove').forEach(function(btn) {
    btn.addEventListener('click', function() { btn.closest('.founder-row').remove(); });
  });
});
</script>
<style>
.dash-form .dash-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--d-text); }
.dash-form .dash-input { width: 100%; padding: 10px 14px; font-size: 0.875rem; border: 1px solid var(--d-border); border-radius: var(--d-radius); background: var(--d-surface); color: var(--d-text); }
.dash-form .dash-input:focus { outline: none; border-color: var(--d-primary); }
.dash-form .dash-error { display: block; margin-top: 4px; font-size: 0.8rem; color: #dc2626; }
@media (max-width: 640px) { .dash-form [style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; } }
</style>

@php
  $isEdit = $startup->exists;
  $formAction = $isEdit ? route('admin.startups.update', $startup) : route('admin.startups.store');
  $formMethod = $isEdit ? 'PUT' : 'POST';
@endphp

<h1 class="dash-page-title">{{ $isEdit ? 'Edit startup' : 'Add startup' }}</h1>
<div class="dash-welcome">
  {{ $isEdit ? 'Update startup details, founder, links and status.' : 'Create a new startup and set the founder, socials and details.' }}
</div>

<form action="{{ $formAction }}" method="post" class="dash-form">
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
    </div>
  </div>

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Founder</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
          <label for="founder_name" class="dash-label">Founder name</label>
          <input type="text" id="founder_name" name="founder_name" value="{{ old('founder_name', $startup->founder_name) }}" class="dash-input" placeholder="Full name">
          @error('founder_name') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
        <div>
          <label for="founder_email" class="dash-label">Founder email</label>
          <input type="email" id="founder_email" name="founder_email" value="{{ old('founder_email', $startup->founder_email) }}" class="dash-input" placeholder="email@example.com">
          @error('founder_email') <span class="dash-error">{{ $message }}</span> @enderror
        </div>
      </div>
      <div>
        <label for="founder_twitter_url" class="dash-label">Founder Twitter / X</label>
        <input type="url" id="founder_twitter_url" name="founder_twitter_url" value="{{ old('founder_twitter_url', $startup->founder_twitter_url) }}" class="dash-input" placeholder="https://twitter.com/...">
        @error('founder_twitter_url') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="founder_linkedin_url" class="dash-label">Founder LinkedIn</label>
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

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Status & visibility</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      @if($isEdit)
      <div>
        <label for="status" class="dash-label">Status</label>
        <select id="status" name="status" class="dash-input">
          <option value="active" {{ old('status', $startup->status) === 'active' ? 'selected' : '' }}>Active</option>
          <option value="disabled" {{ old('status', $startup->status) === 'disabled' ? 'selected' : '' }}>Disabled</option>
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

<style>
.dash-form .dash-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--d-text); }
.dash-form .dash-input { width: 100%; padding: 10px 14px; font-size: 0.875rem; border: 1px solid var(--d-border); border-radius: var(--d-radius); background: var(--d-surface); color: var(--d-text); }
.dash-form .dash-input:focus { outline: none; border-color: var(--d-primary); }
.dash-form .dash-error { display: block; margin-top: 4px; font-size: 0.8rem; color: #dc2626; }
@media (max-width: 640px) { .dash-form [style*="grid-template-columns: 1fr 1fr"] { grid-template-columns: 1fr !important; } }
</style>

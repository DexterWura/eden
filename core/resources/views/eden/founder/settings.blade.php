<h1 class="dash-page-title">Settings</h1>
<div class="dash-welcome">
  Update your profile and password.
</div>

<form action="{{ route('founder.settings.update') }}" method="post" class="dash-form">
  @csrf
  @method('PUT')

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Profile</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <div>
        <label for="name" class="dash-label">Name <span style="color: #dc2626;">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required class="dash-input">
        @error('name') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="email" class="dash-label">Email <span style="color: #dc2626;">*</span></label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required class="dash-input">
        @error('email') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
    </div>
  </div>

  <div class="dash-card" style="margin-bottom: 20px;">
    <div class="dash-card-header"><span class="dash-card-title">Change password</span></div>
    <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 16px;">
      <p style="color: var(--d-text-secondary); font-size: 0.875rem; margin: 0 0 8px;">Leave blank to keep your current password.</p>
      <div>
        <label for="password" class="dash-label">New password</label>
        <input type="password" id="password" name="password" class="dash-input" autocomplete="new-password">
        @error('password') <span class="dash-error">{{ $message }}</span> @enderror
      </div>
      <div>
        <label for="password_confirmation" class="dash-label">Confirm new password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" class="dash-input" autocomplete="new-password">
      </div>
    </div>
  </div>

  <button type="submit" class="dash-btn dash-btn-primary">
    <i class="fa-solid fa-check"></i> Save changes
  </button>
</form>

<style>
.dash-form .dash-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--d-text); }
.dash-form .dash-input { width: 100%; padding: 10px 14px; font-size: 0.875rem; border: 1px solid var(--d-border); border-radius: var(--d-radius); background: var(--d-surface); color: var(--d-text); }
.dash-form .dash-input:focus { outline: none; border-color: var(--d-primary); }
.dash-form .dash-error { display: block; margin-top: 4px; font-size: 0.8rem; color: #dc2626; }
</style>

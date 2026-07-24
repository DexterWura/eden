<h1 class="dash-page-title">Settings</h1>
<div class="dash-welcome">
  Update your profile and password.
</div>

<div class="dash-card" style="margin-bottom:20px;">
  <div class="dash-card-header"><span class="dash-card-title">Account</span></div>
  <div class="dash-card-body" style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;">
    <div><span class="dash-label">Membership</span><strong>{{ $user->isPro() ? 'Pro' : 'Free' }}</strong></div>
    <div><span class="dash-label">Sign-in method</span><strong>{{ $user->auth_provider ? ucfirst($user->auth_provider) : 'Email and password' }}</strong></div>
    <div><span class="dash-label">Member since</span><strong>{{ $user->created_at?->format('M Y') ?? 'Unknown' }}</strong></div>
  </div>
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

  <div class="dash-card" style="margin-bottom:20px;">
    <div class="dash-card-header"><span class="dash-card-title">Email notifications</span></div>
    <div class="dash-card-body" style="display:flex;flex-direction:column;gap:12px;">
      <p style="margin:0;color:var(--d-text-secondary);font-size:.875rem;">In-app notifications remain available in your notification centre.</p>
      @foreach(config('notification_preferences.types', []) as $key => $preference)
      <label style="display:flex;align-items:center;gap:10px;">
        <input type="hidden" name="notification_preferences[{{ $key }}]" value="0">
        <input type="checkbox" name="notification_preferences[{{ $key }}]" value="1" @checked((bool) old("notification_preferences.$key", $user->wantsNotification($key)))>
        <span>{{ $preference['label'] }}</span>
      </label>
      @endforeach
    </div>
  </div>

  <button type="submit" class="dash-btn dash-btn-primary">
    <i class="fa-solid fa-check"></i> Save changes
  </button>
</form>

<div class="dash-card dash-danger-zone" id="danger-zone">
  <div class="dash-card-header"><span class="dash-card-title">Danger zone</span></div>
  <div class="dash-card-body" style="display: flex; flex-direction: column; gap: 14px;">
    <p style="margin: 0; color: var(--d-text-secondary); font-size: 0.875rem;">Delete your founder account and remove your owned apps/posts permanently.</p>
    <form action="{{ route('founder.settings.destroy-data') }}" method="post" class="dash-danger-form" onsubmit="return confirm('This is permanent. Delete your data?');">
      @csrf
      @method('DELETE')
      <label class="dash-danger-confirm">
        <input type="checkbox" name="confirm_delete" value="1" required>
        <span>I understand this action cannot be undone.</span>
      </label>
      <label for="confirm_phrase" class="dash-label">Type <strong>DELETE</strong> to confirm</label>
      <input type="text" id="confirm_phrase" name="confirm_phrase" class="dash-input" placeholder="DELETE" required>
      @error('confirm_delete') <span class="dash-error">{{ $message }}</span> @enderror
      @error('confirm_phrase') <span class="dash-error">{{ $message }}</span> @enderror
      <button type="submit" class="dash-btn dash-btn-danger">
        <i class="fa-solid fa-trash"></i> Delete my data
      </button>
    </form>
  </div>
</div>

<style>
.dash-form .dash-label { display: block; margin-bottom: 6px; font-weight: 500; font-size: 0.875rem; color: var(--d-text); }
.dash-form .dash-input { width: 100%; padding: 10px 14px; font-size: 0.875rem; border: 1px solid var(--d-border); border-radius: var(--d-radius); background: var(--d-surface); color: var(--d-text); }
.dash-form .dash-input:focus { outline: none; border-color: var(--d-primary); }
.dash-form .dash-error { display: block; margin-top: 4px; font-size: 0.8rem; color: #dc2626; }
.dash-danger-zone { border-color: rgba(220, 38, 38, 0.35); }
.dash-danger-form { display: flex; flex-direction: column; gap: 12px; max-width: 460px; }
.dash-danger-confirm { display: inline-flex; align-items: center; gap: 8px; font-size: 0.875rem; color: var(--d-text); }
.dash-danger-confirm input { accent-color: #dc2626; }
</style>

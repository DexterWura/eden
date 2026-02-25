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
    <span class="dash-card-title">More settings</span>
  </div>
  <div class="dash-card-body">
    <div class="dash-placeholder">
      <div class="dash-placeholder-icon"><i class="fa-solid fa-gear"></i></div>
      Site name, logo, and other options can be added here later.
    </div>
  </div>
</div>

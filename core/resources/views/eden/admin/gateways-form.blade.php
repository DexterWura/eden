<h1 class="dash-page-title">Configure {{ $gateway->name }}</h1>

@if($errors->any())
<div style="background:#fee2e2;border:1px solid #fca5a5;border-radius:8px;padding:12px 16px;margin-bottom:20px;color:#991b1b">
  @foreach($errors->all() as $error)
  <p style="margin:0 0 4px">{{ $error }}</p>
  @endforeach
</div>
@endif

<div class="dash-card">
  <div class="dash-card-body">
    <form action="{{ route('admin.gateways.update', $gateway) }}" method="POST">
      @csrf @method('PUT')

      <div class="form-group" style="margin-bottom:24px">
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer">
          <input type="hidden" name="enabled" value="0">
          <input type="checkbox" name="enabled" value="1" {{ $gateway->enabled ? 'checked' : '' }} style="width:20px;height:20px;accent-color:var(--accent,#00d4aa)">
          <span class="form-label" style="margin:0">Enable this gateway</span>
        </label>
      </div>

      <hr style="border:none;border-top:1px solid var(--d-border,#2a2e3d);margin:20px 0">

      <h3 style="margin:0 0 16px;font-size:1rem;font-weight:600">Credentials</h3>

      @if($gateway->alias === 'paypal')
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label" for="param_client_id">Client ID</label>
          <input type="text" name="parameters[client_id]" id="param_client_id" class="form-input" value="{{ $gateway->param('client_id') }}" placeholder="PayPal client ID">
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label" for="param_secret">Secret</label>
          <input type="password" name="parameters[secret]" id="param_secret" class="form-input" value="{{ $gateway->param('secret') }}" placeholder="PayPal secret">
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label" for="param_mode">Mode</label>
          <select name="parameters[mode]" id="param_mode" class="form-input">
            <option value="sandbox" {{ $gateway->param('mode') === 'sandbox' ? 'selected' : '' }}>Sandbox (testing)</option>
            <option value="live" {{ $gateway->param('mode') === 'live' ? 'selected' : '' }}>Live</option>
          </select>
        </div>
      @elseif($gateway->alias === 'paynow')
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label" for="param_integration_id">Integration ID</label>
          <input type="text" name="parameters[integration_id]" id="param_integration_id" class="form-input" value="{{ $gateway->param('integration_id') }}" placeholder="Paynow integration ID">
        </div>
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label" for="param_integration_key">Integration Key</label>
          <input type="password" name="parameters[integration_key]" id="param_integration_key" class="form-input" value="{{ $gateway->param('integration_key') }}" placeholder="Paynow integration key">
        </div>
      @else
        @foreach($gateway->parameters ?? [] as $key => $value)
        <div class="form-group" style="margin-bottom:16px">
          <label class="form-label" for="param_{{ $key }}">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
          <input type="text" name="parameters[{{ $key }}]" id="param_{{ $key }}" class="form-input" value="{{ $value }}">
        </div>
        @endforeach
      @endif

      <div style="display:flex;gap:12px;flex-wrap:wrap;margin-top:24px">
        <button type="submit" class="dash-btn dash-btn-primary">Save changes</button>
        <a href="{{ route('admin.gateways.index') }}" class="dash-btn dash-btn-secondary" style="text-decoration:none">Back to gateways</a>
      </div>
    </form>
  </div>
</div>

<h1 class="dash-page-title">Payment gateways</h1>
<div class="dash-welcome">
  Configure payment gateways for Pro membership purchases.
</div>

<div class="dash-kpi-row">
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Total revenue</div>
    <div class="dash-kpi-value">${{ number_format($totalRevenue, 2) }}</div>
  </div>
  <div class="dash-kpi-card">
    <div class="dash-kpi-label">Pro purchases</div>
    <div class="dash-kpi-value">{{ $totalPayments }}</div>
  </div>
</div>

<div class="dash-card">
  <div class="dash-card-header" style="flex-wrap: wrap; gap: 12px;">
    <span class="dash-card-title">Gateways</span>
    @if($gateways->isEmpty())
    <form action="{{ route('admin.gateways.seed') }}" method="POST" style="margin-left:auto">
      @csrf
      <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-seedling"></i> Seed default gateways</button>
    </form>
    @endif
  </div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Gateway</th>
            <th>Alias</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($gateways as $gw)
          <tr>
            <td>
              @if($gw->alias === 'paypal')
                <i class="fa-brands fa-paypal" style="color:#003087;margin-right:6px"></i>
              @elseif($gw->alias === 'paynow')
                <i class="fa-solid fa-building-columns" style="color:#00d4aa;margin-right:6px"></i>
              @else
                <i class="fa-solid fa-credit-card" style="margin-right:6px"></i>
              @endif
              {{ $gw->name }}
            </td>
            <td><code>{{ $gw->alias }}</code></td>
            <td>
              @if($gw->enabled)
                <span style="display:inline-block;padding:2px 10px;font-size:0.75rem;border-radius:4px;background:#d1fae5;color:#065f46;font-weight:600">Active</span>
              @else
                <span style="display:inline-block;padding:2px 10px;font-size:0.75rem;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:600">Disabled</span>
              @endif
            </td>
            <td>
              <a href="{{ route('admin.gateways.edit', $gw) }}" class="dash-btn dash-btn-secondary" style="padding:4px 10px;font-size:0.8rem;text-decoration:none"><i class="fa-solid fa-gear"></i> Configure</a>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="dash-placeholder">No gateways configured. Click "Seed default gateways" to add PayPal and Paynow.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<h1 class="dash-page-title">Revenue API</h1>
<div class="dash-welcome">
  Link Stripe, a custom payment system, or any revenue source. When you receive a payment, ping Eden so we can track your product&rsquo;s revenue on the leaderboard.
</div>

@if(session('revealed_api_key') && session('revealed_api_key_startup_id'))
<div class="dash-card revenue-api-reveal" style="margin-bottom: 24px; border-color: var(--d-primary);">
  <div class="dash-card-header"><span class="dash-card-title">Your new API key — copy it now</span></div>
  <div class="dash-card-body">
    <p style="color: var(--d-text-secondary); font-size: 0.875rem; margin: 0 0 12px;">This key is shown only once. Store it securely (e.g. in your payment provider&rsquo;s webhook secret or env).</p>
    <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
      <code id="revealedApiKey" class="revenue-api-key-display">{{ session('revealed_api_key') }}</code>
      <button type="button" class="dash-btn dash-btn-primary" onclick="navigator.clipboard.writeText(document.getElementById('revealedApiKey').textContent); this.textContent='Copied!'; setTimeout(function(){ this.textContent='Copy'; }.bind(this), 2000);" style="padding: 6px 12px; font-size: 0.8rem;">Copy</button>
    </div>
  </div>
</div>
@endif

<div class="dash-card" style="margin-bottom: 24px;">
  <div class="dash-card-header"><span class="dash-card-title">API keys per startup</span></div>
  <div class="dash-card-body" style="padding: 0;">
    <div class="dash-table-wrap">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Startup</th>
            <th>API key</th>
            <th>Last used</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($startups as $s)
          <tr>
            <td>
              <a href="{{ url('/startup/' . $s->slug) }}" target="_blank" class="dash-table-link">{{ $s->name }}</a>
            </td>
            <td>
              @if(isset($keysByStartup[$s->id]))
                <code class="revenue-api-key-mask">eden_••••••••••••</code>
              @else
                <span style="color: var(--d-text-secondary); font-size: 0.875rem;">No key</span>
              @endif
            </td>
            <td>
              @if(isset($keysByStartup[$s->id]) && $keysByStartup[$s->id]->last_used_at)
                {{ $keysByStartup[$s->id]->last_used_at->diffForHumans() }}
              @else
                —
              @endif
            </td>
            <td>
              @if(isset($keysByStartup[$s->id]))
                <form action="{{ route('founder.revenue-api.regenerate-key', $s) }}" method="post" style="display: inline;" onsubmit="return confirm('Regenerating will invalidate the current key. Any existing integrations must use the new key. Continue?');">
                  @csrf
                  <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Regenerate</button>
                </form>
              @else
                <form action="{{ route('founder.revenue-api.create-key', $s) }}" method="post" style="display: inline;">
                  @csrf
                  <button type="submit" class="dash-btn dash-btn-primary" style="padding: 4px 10px; font-size: 0.8rem;">Create API key</button>
                </form>
              @endif
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="4" class="dash-placeholder">No startups yet. <a href="{{ route('founder.startups.create') }}">Add a startup</a> first, then create an API key.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="dash-card" style="margin-bottom: 24px;">
  <div class="dash-card-header"><span class="dash-card-title">Automatic tracking</span></div>
  <div class="dash-card-body">
    <p style="color: var(--d-text-secondary); font-size: 0.875rem; margin: 0 0 16px;">Connect Stripe, Polar, or Lemon Squeezy with a <strong>read-only</strong> API key. Eden syncs revenue automatically — no webhooks or manual POSTs. Use restricted keys where possible (e.g. <a href="https://dashboard.stripe.com/apikeys" target="_blank" rel="noopener">Stripe</a> with read-only access to Charges).</p>
    @forelse($startups as $s)
    <div class="revenue-integration-block" style="margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1px solid var(--d-border);">
      <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
        <strong><a href="{{ url('/startup/' . $s->slug) }}" class="dash-table-link">{{ $s->name }}</a></strong>
        @php $ints = $integrationsByStartup[$s->id] ?? collect(); @endphp
        @if($ints->isNotEmpty())
        <form action="{{ route('founder.revenue-api.sync', $s) }}" method="post" style="display: inline;">
          @csrf
          <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Sync now</button>
        </form>
        @endif
      </div>
      <div class="revenue-integration-grid" style="display: flex; flex-wrap: wrap; gap: 12px;">
        @foreach(\App\Models\StartupRevenueIntegration::GATEWAYS as $g => $meta)
        @php $integ = $ints->firstWhere('gateway', $g); @endphp
        <div class="revenue-integration-item" style="flex: 1; min-width: 220px; max-width: 320px; padding: 12px; border: 1px solid var(--d-border); border-radius: var(--d-radius); background: var(--d-surface);">
          <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <i class="fa {{ $meta['icon'] }}" style="color: var(--d-primary);"></i>
            <strong>{{ $meta['name'] }}</strong>
          </div>
          @if($integ)
          <p style="font-size: 0.8rem; color: var(--d-text-secondary); margin: 0 0 8px;">
            Last sync: {{ $integ->last_synced_at ? $integ->last_synced_at->diffForHumans() : 'Never' }}
            @if($integ->last_sync_status)
            <br><span class="revenue-sync-status">{{ $integ->last_sync_status }}</span>
            @endif
          </p>
          <form action="{{ route('founder.revenue-api.disconnect-integration', [$s, $g]) }}" method="post" style="display: inline;" onsubmit="return confirm('Disconnect {{ $meta['name'] }}? You can reconnect later.');">
            @csrf
            @method('DELETE')
            <button type="submit" class="dash-btn dash-btn-secondary" style="padding: 4px 10px; font-size: 0.8rem;">Disconnect</button>
          </form>
          @else
          <form action="{{ route('founder.revenue-api.connect-integration', $s) }}" method="post">
            @csrf
            <input type="hidden" name="gateway" value="{{ $g }}">
            <div style="margin-bottom: 8px;">
              <label class="revenue-integration-label" style="display: block; font-size: 0.75rem; color: var(--d-text-secondary); margin-bottom: 4px;">Read-only API key</label>
              <input type="password" name="api_key" placeholder="sk_live_… / sk_test_…" required class="revenue-api-input" style="width: 100%; padding: 6px 10px; font-size: 0.85rem; border: 1px solid var(--d-border); border-radius: var(--d-radius); background: var(--d-bg); color: var(--d-text);">
            </div>
            <button type="submit" class="dash-btn dash-btn-primary" style="padding: 4px 10px; font-size: 0.8rem;">Connect</button>
          </form>
          @endif
        </div>
        @endforeach
      </div>
    </div>
    @empty
    <p class="dash-placeholder">No startups yet. <a href="{{ route('founder.startups.create') }}">Add a startup</a> first.</p>
    @endforelse
  </div>
</div>

<div class="dash-card" style="margin-bottom: 24px;">
  <div class="dash-card-header"><span class="dash-card-title">Documentation</span></div>
  <div class="dash-card-body revenue-api-docs">
    <h3 style="font-size: 1rem; margin: 0 0 12px;">Endpoint</h3>
    <p style="margin: 0 0 8px; font-size: 0.875rem; color: var(--d-text-secondary);">Record a payment so Eden updates your startup&rsquo;s revenue (and optionally MRR).</p>
    <pre class="revenue-api-code">POST {{ $apiBaseUrl }}/revenue</pre>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Authentication</h3>
    <p style="margin: 0 0 8px; font-size: 0.875rem; color: var(--d-text-secondary);">Send your API key in one of two ways:</p>
    <ul style="margin: 0 0 12px; padding-left: 20px; font-size: 0.875rem;">
      <li><strong>Authorization header:</strong> <code>Authorization: Bearer YOUR_API_KEY</code></li>
      <li><strong>Custom header:</strong> <code>X-Eden-API-Key: YOUR_API_KEY</code></li>
    </ul>
    <p style="margin: 0 0 12px; font-size: 0.875rem; color: var(--d-text-secondary);">The key is tied to one startup. Never expose it in client-side code or public repos.</p>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Request body (JSON)</h3>
    <table class="revenue-api-params">
      <thead>
        <tr><th>Field</th><th>Type</th><th>Required</th><th>Description</th></tr>
      </thead>
      <tbody>
        <tr><td><code>amount</code></td><td>number</td><td>Yes</td><td>Payment amount (non-negative)</td></tr>
        <tr><td><code>currency</code></td><td>string</td><td>Yes</td><td>3-letter ISO code (e.g. <code>USD</code>, <code>ZWL</code>)</td></tr>
        <tr><td><code>external_id</code></td><td>string</td><td>No</td><td>Your payment ID for idempotency (prevents double-counting)</td></tr>
        <tr><td><code>mrr</code></td><td>number</td><td>No</td><td>Current monthly recurring revenue (overwrites stored MRR)</td></tr>
      </tbody>
    </table>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Idempotency</h3>
    <p style="margin: 0 0 12px; font-size: 0.875rem; color: var(--d-text-secondary);">If you send the same <code>external_id</code> twice, the second request returns success without adding the amount again. Always send your payment or invoice ID so retries don&rsquo;t double-count.</p>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Rate limit</h3>
    <p style="margin: 0 0 12px; font-size: 0.875rem; color: var(--d-text-secondary);">60 requests per minute per API key. Responses use standard HTTP status codes (200, 201, 401, 403, 422, 429).</p>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Example: cURL</h3>
    <pre class="revenue-api-code">curl -X POST "{{ $apiBaseUrl }}/revenue" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -d '{"amount": 99.00, "currency": "USD", "external_id": "pay_abc123"}'</pre>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Example: Stripe webhook</h3>
    <p style="margin: 0 0 8px; font-size: 0.875rem; color: var(--d-text-secondary);">In your Stripe webhook (e.g. <code>checkout.session.completed</code> or <code>invoice.paid</code>), after validating the event, call Eden:</p>
    <pre class="revenue-api-code">// Node.js example
const amount = event.data.object.amount_paid / 100; // cents to dollars
await fetch('{{ $apiBaseUrl }}/revenue', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Authorization': `Bearer ${process.env.EDEN_REVENUE_API_KEY}`,
  },
  body: JSON.stringify({
    amount,
    currency: (event.data.object.currency || 'usd').toUpperCase(),
    external_id: event.id, // Stripe event ID = idempotency
  }),
});</pre>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Example: Custom payment system</h3>
    <p style="margin: 0 0 8px; font-size: 0.875rem; color: var(--d-text-secondary);">When your backend confirms a payment (Stripe, PayPal, custom gateway, or manual), send a server-side POST with the same JSON. Use <code>external_id</code> as your internal payment or invoice ID.</p>
    <pre class="revenue-api-code">// PHP example
$ch = curl_init('{{ $apiBaseUrl }}/revenue');
curl_setopt_array($ch, [
  CURLOPT_POST => true,
  CURLOPT_POSTFIELDS => json_encode([
    'amount' => 49.99,
    'currency' => 'USD',
    'external_id' => 'inv_' . $invoiceId,
  ]),
  CURLOPT_HTTPHEADER => [
    'Content-Type: application/json',
    'Authorization: Bearer ' . getenv('EDEN_REVENUE_API_KEY'),
  ],
  CURLOPT_RETURNTRANSFER => true,
]);
$response = curl_exec($ch);
// Check HTTP status: 201 = recorded, 200 = idempotent duplicate</pre>

    <h3 style="font-size: 1rem; margin: 24px 0 12px;">Responses</h3>
    <ul style="margin: 0; padding-left: 20px; font-size: 0.875rem;">
      <li><strong>201 Created</strong> — Payment recorded. Body includes <code>event_id</code>, <code>revenue_total</code>.</li>
      <li><strong>200 OK</strong> — Duplicate <code>external_id</code> (idempotent). No double-count.</li>
      <li><strong>401 Unauthorized</strong> — Missing or invalid API key.</li>
      <li><strong>403 Forbidden</strong> — Startup not active.</li>
      <li><strong>422 Unprocessable Entity</strong> — Validation error (e.g. missing <code>amount</code> or <code>currency</code>).</li>
      <li><strong>429 Too Many Requests</strong> — Rate limit exceeded.</li>
    </ul>
  </div>
</div>

<style>
.revenue-api-key-display { font-size: 0.9rem; padding: 10px 14px; background: var(--d-surface); border: 1px solid var(--d-border); border-radius: var(--d-radius); word-break: break-all; }
.revenue-api-key-mask { font-size: 0.8rem; color: var(--d-text-secondary); }
.revenue-api-docs h3 { font-weight: 600; }
.revenue-api-docs pre.revenue-api-code { background: var(--d-surface); border: 1px solid var(--d-border); border-radius: var(--d-radius); padding: 12px 16px; font-size: 0.8rem; overflow-x: auto; margin: 0 0 12px; white-space: pre-wrap; word-break: break-word; }
.revenue-api-docs .revenue-api-params { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
.revenue-api-docs .revenue-api-params th, .revenue-api-docs .revenue-api-params td { text-align: left; padding: 8px 12px; border-bottom: 1px solid var(--d-border); }
.revenue-api-docs .revenue-api-params code { font-size: 0.85em; }
</style>

<h1 class="dash-page-title">Fund raising</h1>
<div class="dash-welcome">
  Manage investor-facing fund raising details for each startup you own. Open a round, update the ask, or close it when you are done.
</div>

@php
  $startups = $startups ?? collect();
  $fundingRoundTypes = $fundingRoundTypes ?? [];
@endphp

@if($startups->isEmpty())
<div class="dash-card">
  <div class="dash-card-body" style="text-align:center; padding: 40px 24px;">
    <p style="color: var(--d-text-secondary); font-size: 1rem; margin-bottom: 16px;">Add a startup first before managing fund raising.</p>
    <a href="{{ route('founder.startups.create') }}" class="dash-btn dash-btn-primary" style="text-decoration: none;"><i class="fa-solid fa-plus"></i> Add startup</a>
  </div>
</div>
@else
<div style="display:flex; flex-direction:column; gap:20px;">
  @foreach($startups as $startup)
  @php
    $round = $startup->activeFundingRound;
    $investorLeads = $startup->fundingRounds
      ->flatMap(fn ($fundingRound) => $fundingRound->investorLeads)
      ->sortByDesc('created_at');
    $enabled = old('startup_id') == $startup->id
      ? old('seeking_investors', $round ? '1' : '0') === '1'
      : (bool) $round;
    $roundType = old('startup_id') == $startup->id ? old('funding_round_type', $round?->round_type ?? 'seed') : ($round?->round_type ?? 'seed');
    $amount = old('startup_id') == $startup->id ? old('funding_amount_seeking', $round?->amount_seeking) : $round?->amount_seeking;
    $currency = old('startup_id') == $startup->id ? old('funding_currency', $round?->currency ?? 'USD') : ($round?->currency ?? 'USD');
    $contactEmail = old('startup_id') == $startup->id ? old('funding_contact_email', $round?->contact_email ?? $startup->founder_email) : ($round?->contact_email ?? $startup->founder_email);
    $description = old('startup_id') == $startup->id ? old('funding_description', $round?->description) : $round?->description;
  @endphp
  <div class="dash-card" style="border-left: 4px solid {{ $round ? '#16a34a' : '#6366f1' }};">
    <div class="dash-card-header" style="flex-wrap:wrap; gap:12px;">
      <div>
        <span class="dash-card-title"><i class="fa-solid fa-hand-holding-dollar"></i> {{ $startup->name }}</span>
        <div class="dash-card-subtitle" style="margin-top:4px;">
          @if($round)
            Live on <a href="{{ url('/startup/' . $startup->slug) }}" target="_blank" class="dash-table-link">your public startup page</a>.
          @else
            Not currently shown as raising on the public listing.
          @endif
        </div>
      </div>
      <div style="display:flex; align-items:center; gap:8px; margin-left:auto; flex-wrap:wrap;">
        @if($startup->status === 'pending')
        <span style="display:inline-block;padding:3px 10px;font-size:0.78rem;border-radius:4px;background:#fef3c7;color:#92400e;font-weight:600">Pending review</span>
        @else
        <span style="display:inline-block;padding:3px 10px;font-size:0.78rem;border-radius:4px;background:#dbeafe;color:#1d4ed8;font-weight:600">{{ ucfirst($startup->status ?? 'active') }}</span>
        @endif
        @if($round)
        <span style="display:inline-block;padding:3px 10px;font-size:0.78rem;border-radius:4px;background:#dcfce7;color:#166534;font-weight:600">{{ $round->round_type_label }}</span>
        @endif
        <a href="{{ route('founder.startups.edit', $startup) }}" class="dash-btn dash-btn-secondary" style="text-decoration:none; padding:4px 10px; font-size:0.8rem;"><i class="fa-solid fa-pen"></i> Edit startup</a>
      </div>
    </div>
    <div class="dash-card-body">
      <form action="{{ route('founder.fundraising.update', $startup) }}" method="POST" class="dash-form">
        @csrf
        <input type="hidden" name="startup_id" value="{{ $startup->id }}">
        <div style="display:flex; flex-direction:column; gap:16px;">
          <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
            <input type="hidden" name="seeking_investors" value="0">
            <input type="checkbox" name="seeking_investors" value="1" class="js-funding-toggle" data-target="funding-fields-{{ $startup->id }}" {{ $enabled ? 'checked' : '' }}>
            <span class="dash-label" style="margin-bottom:0;">We are raising funding / looking for investors</span>
          </label>

          <div id="funding-fields-{{ $startup->id }}" style="{{ $enabled ? '' : 'display:none;' }}">
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:16px;">
              <div>
                <label for="funding_round_type_{{ $startup->id }}" class="dash-label">Round type</label>
                <select id="funding_round_type_{{ $startup->id }}" name="funding_round_type" class="dash-input">
                  @foreach($fundingRoundTypes as $value => $label)
                  <option value="{{ $value }}" {{ $roundType === $value ? 'selected' : '' }}>{{ $label }}</option>
                  @endforeach
                </select>
              </div>
              <div>
                <label for="funding_amount_seeking_{{ $startup->id }}" class="dash-label">Amount seeking</label>
                <input type="number" id="funding_amount_seeking_{{ $startup->id }}" name="funding_amount_seeking" value="{{ $amount }}" class="dash-input" placeholder="e.g. 500000" min="0" step="0.01">
              </div>
              <div>
                <label for="funding_currency_{{ $startup->id }}" class="dash-label">Currency</label>
                <input type="text" id="funding_currency_{{ $startup->id }}" name="funding_currency" value="{{ $currency }}" class="dash-input" placeholder="USD" maxlength="3">
              </div>
              <div>
                <label for="funding_contact_email_{{ $startup->id }}" class="dash-label">Investor contact email</label>
                <input type="email" id="funding_contact_email_{{ $startup->id }}" name="funding_contact_email" value="{{ $contactEmail }}" class="dash-input" placeholder="investors@example.com">
              </div>
            </div>

            <div>
              <label for="funding_description_{{ $startup->id }}" class="dash-label">Description</label>
              <textarea id="funding_description_{{ $startup->id }}" name="funding_description" rows="4" class="dash-input" placeholder="Brief pitch, traction, use of funds, or investor notes.">{{ $description }}</textarea>
            </div>
          </div>

          @if($round)
          <p class="dash-hint" style="margin:0; color:var(--d-text-secondary);">Current public listing: {{ $round->round_type_label }}@if($round->amount_seeking) · {{ number_format((float) $round->amount_seeking, 0) }} {{ $round->currency }}@endif</p>
          @endif

          @if($errors->any() && old('startup_id') == $startup->id)
          <div class="dash-error" style="display:block;">{{ $errors->first() }}</div>
          @endif

          <div style="display:flex; gap:10px; flex-wrap:wrap;">
            <button type="submit" class="dash-btn dash-btn-primary"><i class="fa-solid fa-check"></i> Save fund raising</button>
            <a href="{{ url('/startup/' . $startup->slug) }}" target="_blank" class="dash-btn dash-btn-secondary" style="text-decoration:none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> View public page</a>
          </div>
        </div>
      </form>
      @if($investorLeads->isNotEmpty())
      <section style="margin-top:24px;border-top:1px solid var(--d-border);padding-top:20px">
        <h3 class="dash-card-title">Investor inbox</h3>
        @foreach($investorLeads as $lead)
        <article style="padding:14px 0;border-bottom:1px solid var(--d-border)">
          <div style="display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap">
            <div>
              <strong>{{ $lead->name }}</strong>
              <a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a>
              @if($lead->organization) · {{ $lead->organization }} @endif
              @if($lead->message)<p style="margin:8px 0">{{ $lead->message }}</p>@endif
            </div>
            <span class="dash-badge">{{ ucfirst($lead->status) }}</span>
          </div>
          <form method="POST" action="{{ route('founder.fundraising.leads.update', $lead) }}" style="display:grid;grid-template-columns:minmax(220px,1fr) 150px auto;gap:8px;align-items:end">
            @csrf @method('PATCH')
            <label class="dash-label">Private notes
              <textarea class="dash-input" name="notes" rows="2" maxlength="3000">{{ $lead->notes }}</textarea>
            </label>
            <label class="dash-label">Status
              <select class="dash-input" name="status">
                @foreach(\App\Models\InvestorLead::STATUSES as $status)
                <option value="{{ $status }}" {{ $lead->status === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>
                @endforeach
              </select>
            </label>
            <button class="dash-btn dash-btn-secondary" type="submit">Update lead</button>
          </form>
        </article>
        @endforeach
      </section>
      @endif
    </div>
  </div>
  @endforeach
</div>

<script>
(function() {
  document.querySelectorAll('.js-funding-toggle').forEach(function(toggle) {
    toggle.addEventListener('change', function() {
      var targetId = toggle.getAttribute('data-target');
      var target = targetId ? document.getElementById(targetId) : null;
      if (!target) return;
      target.style.display = toggle.checked ? '' : 'none';
    });
  });
})();
</script>
@endif

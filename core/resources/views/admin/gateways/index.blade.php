@extends('layouts.admin')

@section('title', 'Gateways & Pro features')

@section('content')
    <h1>Gateways & Pro features</h1>
    <p class="page-sub">Configure payment gateways (PayPal, PayNow) and set prices for pro features (e.g. Blogging).</p>

    <form method="POST" action="{{ route('admin.gateways.update') }}" class="form-max-600">
        @csrf
        @method('PUT')

        <h2 class="section-title-sm">PayPal</h2>
        <div class="form-group">
            <label>PayPal Client ID</label>
            <input type="text" name="paypal_client_id" value="{{ old('paypal_client_id', $paypal_client_id) }}" placeholder="From PayPal Developer Dashboard">
        </div>
        <div class="form-group">
            <label>PayPal Secret</label>
            <input type="password" name="paypal_secret" value="{{ old('paypal_secret', $paypal_secret) }}" placeholder="Secret key" autocomplete="off">
        </div>

        <h2 class="section-title-sm">PayNow Zimbabwe</h2>
        <div class="form-group">
            <label>PayNow Integration ID</label>
            <input type="text" name="paynow_integration_id" value="{{ old('paynow_integration_id', $paynow_integration_id) }}" placeholder="From PayNow merchant dashboard">
        </div>
        <div class="form-group">
            <label>PayNow Integration Key</label>
            <input type="password" name="paynow_integration_key" value="{{ old('paynow_integration_key', $paynow_integration_key) }}" placeholder="Integration key" autocomplete="off">
        </div>

        <h2 class="section-title-sm">Features: Pro vs free</h2>
        <p class="admin-alert admin-alert-success" style="margin-bottom:1rem;">Mark each feature as &quot;Pro&quot; (paid) or free. When &quot;Require payment&quot; is unchecked, everyone has access. When checked, set the price and users pay once to unlock.</p>
        @foreach($features as $key => $info)
            <div class="form-group" style="padding:1rem 0; border-bottom:1px solid var(--ga-border, #eee);">
                <label style="display:flex;align-items:center;gap:0.5rem;">
                    <input type="hidden" name="pro_feature_{{ $key }}_is_pro" value="0">
                    <input type="checkbox" name="pro_feature_{{ $key }}_is_pro" value="1" {{ old("pro_feature_{$key}_is_pro", $isPro[$key] ?? true) ? 'checked' : '' }}>
                    <strong>{{ $info['name'] }}</strong> ({{ $key }})
                </label>
                <p style="margin:0.25rem 0 0.5rem 0;color:var(--ga-text-secondary,#5f6368);font-size:13px;">Require payment (pro feature)</p>
                @if(!empty($info['description']))<small class="form-hint">{{ $info['description'] }}</small>@endif
                <div style="margin-top:0.5rem;">
                    <label style="font-weight:normal;">Price (USD) when pro</label>
                    <input type="number" name="pro_feature_{{ $key }}_price" value="{{ old("pro_feature_{$key}_price", $prices[$key] ?? 0) }}" min="0" step="0.01">
                </div>
            </div>
        @endforeach

        <button type="submit" class="btn">Save</button>
    </form>
@endsection

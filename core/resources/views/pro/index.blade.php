@extends('layouts.app')

@section('title', 'Upgrade to Pro')

@section('content')
    <h1>Upgrade to Pro</h1>
    <p>Unlock paid features with a one-time payment. Choose a feature and payment method below.</p>

    @if(empty($features))
        <p class="admin-alert admin-alert-success">There are no paid pro features at the moment. All features are free for everyone.</p>
        <p><a href="{{ route('home') }}">Back to home</a></p>
    @elseif(collect($features)->every(fn($_, $k) => auth()->user()->hasProFeature($k)))
        <p class="admin-alert admin-alert-success">You already have access to all pro features.</p>
        <p><a href="{{ route('home') }}">Back to home</a></p>
    @else
    <form method="POST" action="{{ route('pro.checkout') }}" class="form-max-600" style="max-width: 32rem;">
        @csrf

        <div class="form-group">
            <label>Feature</label>
            <select name="feature_key" required>
                @foreach($features as $key => $info)
                    @if(!auth()->user()->hasProFeature($key))
                        <option value="{{ $key }}" data-currency-usd="{{ $prices[$key]['USD'] ?? 0 }}" data-currency-zwl="{{ $prices[$key]['ZWL'] ?? 0 }}">
                            {{ $info['name'] }} — ${{ number_format($prices[$key]['USD'] ?? 0, 2) }} / ZWL {{ number_format($prices[$key]['ZWL'] ?? 0, 2) }}
                        </option>
                    @endif
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Currency</label>
            <select name="currency">
                @foreach($currencies as $code => $name)
                    <option value="{{ $code }}">{{ $code }} — {{ $name }}</option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Payment method</label>
            <select name="gateway" required>
                @foreach($gateways as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                @endforeach
            </select>
        </div>

        <button type="submit" class="btn">Continue to payment</button>
    </form>

    <p style="margin-top: 1.5rem;"><a href="{{ route('home') }}">Back to home</a></p>
    @endif
@endsection

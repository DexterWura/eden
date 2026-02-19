@extends('layouts.app')

@section('title', 'Claim: ' . $startup->name)

@section('content')
    <h1>Claim: {{ $startup->name }}</h1>
    <p>Add this meta tag to your site <strong>{{ $startup->url }}</strong> (in the <code>&lt;head&gt;</code> section):</p>
    <pre class="code-block"><code>&lt;meta name="eden-verification" content="{{ $claim->verification_token }}"&gt;</code></pre>
    <p>Then click Verify to confirm ownership.</p>
    <form method="POST" action="{{ route('claim.verify', $claim) }}">
        @csrf
        <button type="submit" class="btn">Verify</button>
    </form>
    <p class="back-link"><a href="{{ route('startups.show', $startup->slug) }}">Back to startup</a></p>
@endsection

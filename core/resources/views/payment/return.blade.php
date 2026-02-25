@extends('layouts.app')

@section('title', $success ? 'Payment successful' : 'Payment status')

@section('content')
    @if($success)
        <h1>Payment successful</h1>
        <p>Thank you. Your payment has been confirmed and you now have access to the pro feature.</p>
        @if($payment->feature_key === 'blogging')
            <p><a href="{{ route('my.blog.index') }}" class="btn">Go to my blog</a></p>
        @endif
    @else
        <h1>Payment pending</h1>
        <p>Your payment may still be processing. If you completed payment, it will be confirmed shortly. You can also refresh this page to check again.</p>
        <p><a href="{{ route('pro.index') }}" class="btn">Back to upgrade</a> &nbsp; <a href="{{ route('home') }}">Home</a></p>
    @endif
@endsection

@extends('layouts.app')

@section('title', 'Unsubscribed')

@section('content')
    <div class="auth-box">
        <h1>Unsubscribed</h1>
        <p>You have been removed from the newsletter. You can subscribe again anytime from the footer.</p>
        <p class="back-link"><a href="{{ route('home') }}">Back to home</a></p>
    </div>
@endsection

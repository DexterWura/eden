@extends('layouts.app')

@section('title', 'Forgot password')

@section('content')
    <div class="auth-box">
        <h1>Forgot password</h1>
        @if(session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <button type="submit" class="btn">Send reset link</button>
        </form>
        <p class="back-link"><a href="{{ route('login') }}">Back to login</a></p>
    </div>
@endsection

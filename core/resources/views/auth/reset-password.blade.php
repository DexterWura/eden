@extends('layouts.app')

@section('title', 'Reset password')

@section('content')
    <div class="auth-box">
        <h1>Reset password</h1>
        @if($errors->any())
            <div class="alert alert-error">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('password.update') }}">
            @csrf
            <input type="hidden" name="token" value="{{ $token }}">
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $email) }}" required>
            </div>
            <div class="form-group">
                <label for="password">New password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="password_confirmation">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required>
            </div>
            <button type="submit" class="btn">Reset password</button>
        </form>
        <p class="back-link"><a href="{{ route('login') }}">Back to login</a></p>
    </div>
@endsection

@extends('layouts.app')

@section('title', 'Login')

@section('content')
    <div class="auth-box">
        <h1>Login</h1>
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" type="password" name="password" required>
            </div>
            <div class="form-group">
                <label><input type="checkbox" name="remember" value="1"> Remember me</label>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
        <p class="back-link"><a href="{{ route('register') }}">Register</a></p>
    </div>
@endsection

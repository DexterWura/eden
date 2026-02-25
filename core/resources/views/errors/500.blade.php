@extends('errors.eden-layout')
@section('content')
  <div class="error-page-code">500</div>
  <h1 class="error-page-title">Something went wrong</h1>
  <p class="error-page-desc">{{ $message ?? 'An unexpected error occurred. We\'ve been notified and are working on it.' }}</p>
  <a href="{{ url('/') }}" class="btn btn-primary">Go to home</a>
@endsection

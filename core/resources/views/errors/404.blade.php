@extends('errors.eden-layout')
@section('content')
  <div class="error-page-code">404</div>
  <h1 class="error-page-title">Page not found</h1>
  <p class="error-page-desc">The page you're looking for doesn't exist or has been moved.</p>
  <a href="{{ url('/') }}" class="btn btn-primary">Go to home</a>
@endsection

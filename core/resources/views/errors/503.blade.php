@extends('errors.eden-layout')
@section('content')
  <div class="error-page-code">503</div>
  <h1 class="error-page-title">Temporarily unavailable</h1>
  <p class="error-page-desc">We're doing some maintenance. Please try again in a few minutes.</p>
  <a href="{{ url('/') }}" class="btn btn-primary">Go to home</a>
@endsection

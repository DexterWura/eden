@extends('errors.eden-layout')
@section('content')
  <div class="error-page-code">419</div>
  <h1 class="error-page-title">Page expired</h1>
  <p class="error-page-desc">Your session expired. Please refresh the page and try again.</p>
  <a href="{{ url('/') }}" class="btn btn-primary">Go to home</a>
@endsection

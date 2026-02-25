@extends('errors.eden-layout')
@section('content')
  <div class="error-page-code">429</div>
  <h1 class="error-page-title">Too many requests</h1>
  <p class="error-page-desc">You've made too many requests. Please wait a moment and try again.</p>
  <a href="{{ url('/') }}" class="btn btn-primary">Go to home</a>
@endsection

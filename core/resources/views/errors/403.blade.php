@extends('errors.eden-layout')
@section('content')
  <div class="error-page-code">403</div>
  <h1 class="error-page-title">Access denied</h1>
  <p class="error-page-desc">You don't have permission to view this page.</p>
  <a href="{{ url('/') }}" class="btn btn-primary">Go to home</a>
@endsection

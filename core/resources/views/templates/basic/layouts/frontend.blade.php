@extends($activeTemplate . 'layouts.app')
@section('panel')
    @include($activeTemplate . 'partials.header')
    <main class="main-wrapper">
        @if (!request()->routeIs('home'))
            @include($activeTemplate . 'partials.breadcrumb')
        @endif
        @yield('content')
    </main>
    @include($activeTemplate . 'partials.footer')
@endsection

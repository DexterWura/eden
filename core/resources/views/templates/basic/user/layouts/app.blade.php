@extends($activeTemplate . 'user.layouts.master')
@section('content')
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include($activeTemplate . 'user.partials.sidenav')
        @include($activeTemplate . 'user.partials.topnav')

        <div class="container-fluid px-3 px-sm-0">
            <div class="body-wrapper">
                <div class="bodywrapper__inner">

                    @stack('topBar')
                    @include($activeTemplate . 'user.partials.breadcrumb')

                    @yield('panel')

                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>
    </div>
@endsection


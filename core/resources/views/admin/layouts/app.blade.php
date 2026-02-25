@extends('admin.layouts.master')
@section('content')
    <!-- page-wrapper start -->
    <div class="page-wrapper default-version">
        @include('admin.partials.sidenav')
        @include('admin.partials.topnav')

        <div class="container-fluid px-3 px-sm-0">
            <div class="body-wrapper">
                <div class="bodywrapper__inner">

                    @stack('topBar')
                    @include('admin.partials.breadcrumb')

                    @yield('panel')

                </div><!-- bodywrapper__inner end -->
            </div><!-- body-wrapper end -->
        </div>
    </div>
@endsection

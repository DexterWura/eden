@extends('admin.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">@lang('Direct Package Download')</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning">
                        <strong>@lang('Note:')</strong> @lang('This will download and install individual packages directly. Use this if Composer installation fails.')
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0">@lang('DomPDF Core Library')</h6>
                                </div>
                                <div class="card-body">
                                    <p>@lang('Required for PDF generation functionality.')</p>
                                    <form action="{{ route('admin.system.composer.package', 'dompdf') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="las la-download"></i> @lang('Download DomPDF')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0">@lang('Laravel DomPDF Bridge')</h6>
                                </div>
                                <div class="card-body">
                                    <p>@lang('Laravel integration for DomPDF.')</p>
                                    <form action="{{ route('admin.system.composer.package', 'laravel-dompdf') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="las la-download"></i> @lang('Download Laravel DomPDF')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">@lang('SVG Library')</h6>
                                </div>
                                <div class="card-body">
                                    <p>@lang('Required for SVG support in PDFs.')</p>
                                    <form action="{{ route('admin.system.composer.package', 'php-svg-lib') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-info w-100">
                                            <i class="las la-download"></i> @lang('Download SVG Lib')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-white">
                                    <h6 class="mb-0">@lang('Font Library')</h6>
                                </div>
                                <div class="card-body">
                                    <p>@lang('Required for font handling in PDFs.')</p>
                                    <form action="{{ route('admin.system.composer.package', 'php-font-lib') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-warning w-100">
                                            <i class="las la-download"></i> @lang('Download Font Lib')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-secondary">
                                <div class="card-header bg-secondary text-white">
                                    <h6 class="mb-0">@lang('CSS Parser')</h6>
                                </div>
                                <div class="card-body">
                                    <p>@lang('Required for CSS parsing in PDFs.')</p>
                                    <form action="{{ route('admin.system.composer.package', 'php-css-parser') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-secondary w-100">
                                            <i class="las la-download"></i> @lang('Download CSS Parser')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-info">
                            <strong>@lang('Installation Order:')</strong><br>
                            @lang('Install in this order: 1) DomPDF, 2) Laravel DomPDF, 3) SVG Lib, 4) Font Lib, 5) CSS Parser')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

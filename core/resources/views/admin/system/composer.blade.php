@extends('admin.layouts.app')
@section('panel')
    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('Missing composer dependencies will be installed')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('DomPDF package for PDF generation will be installed')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('All vendor packages will be updated to latest versions')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('Autoloader will be optimized for better performance')</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
                            <span><i class="las la-check-double text--success"></i> @lang('System cache will be cleared after installation')</span>
                        </li>
                    </ul>
                </div>
                <div class="card-footer">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <form action="{{ route('admin.system.composer.download') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn--warning w-100 h-45">
                                    <i class="las la-cloud-download-alt"></i> @lang('Download Composer.phar')
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.system.composer.install') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn--primary w-100 h-45">
                                    <i class="las la-download"></i> @lang('Composer Install')
                                </button>
                            </form>
                        </div>
                        <div class="col-md-4">
                            <form action="{{ route('admin.system.composer.manual') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn--success w-100 h-45">
                                    <i class="las la-tools"></i> @lang('Manual Install')
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="alert alert-info">
                        <strong>@lang('Installation Options:')</strong><br>
                        1. @lang('Download Composer.phar first, then use Composer Install')<br>
                        2. @lang('If Composer Install fails, try Manual Install (downloads all packages at once)')<br>
                        3. @lang('Or use Direct Download to install packages individually')<br><br>
                        <a href="{{ route('admin.system.composer.direct') }}" class="btn btn-sm btn-outline-primary">
                            <i class="las la-external-link-alt"></i> @lang('Go to Direct Download Page')
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('style')
<style>
  .list-group-item span{
    font-size: 22px !important;
    padding: 8px 0px
  }
</style>
@endpush

@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12">
            {{-- Sitemap Statistics --}}
            @if(isset($stats))
            <div class="card mb-4">
                <div class="card-header">
                    <h6>@lang('Sitemap Statistics')</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 col-6">
                            <div class="stat-card text-center p-3 bg--primary rounded">
                                <h3 class="text-white mb-1">{{ $stats['total_urls'] ?? 0 }}</h3>
                                <p class="text-white mb-0">@lang('Total URLs')</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card text-center p-3 bg--success rounded">
                                <h3 class="text-white mb-1">{{ $stats['listings'] ?? 0 }}</h3>
                                <p class="text-white mb-0">@lang('Listings')</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card text-center p-3 bg--info rounded">
                                <h3 class="text-white mb-1">{{ $stats['categories'] ?? 0 }}</h3>
                                <p class="text-white mb-0">@lang('Categories')</p>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="stat-card text-center p-3 bg--warning rounded">
                                <h3 class="text-white mb-1">{{ ($stats['blogs'] ?? 0) + ($stats['pages'] ?? 0) }}</h3>
                                <p class="text-white mb-0">@lang('Blogs & Pages')</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Dynamic Sitemap Generation --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6>@lang('Dynamic Sitemap Generation')</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        @lang('The sitemap is automatically generated from your active listings, categories, blogs, and pages. Click the button below to regenerate it.')
                    </p>
                    <div class="d-flex gap-2 flex-wrap">
                        <form action="{{ route('admin.setting.sitemap.regenerate') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn--primary">
                                <i class="las la-sync"></i> @lang('Regenerate Sitemap')
                            </button>
                        </form>
                        <a href="{{ route('sitemap') }}" target="_blank" class="btn btn--info">
                            <i class="las la-external-link-alt"></i> @lang('View Live Sitemap')
                        </a>
                    </div>
                </div>
            </div>

            {{-- Sitemap XML Editor --}}
            <div class="card">
                <div class="card-header">
                    <h6>@lang('Sitemap XML Editor')</h6>
                    <p class="text-muted mb-0 small">@lang('You can manually edit the sitemap XML below. The sitemap is automatically generated, but you can customize it if needed.')</p>
                </div>
                <form method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group custom-css">
                            <textarea class="form-control sitemapEditor" rows="10" name="sitemap">{{ $fileContent ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save Sitemap')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@push('style')
<style>
    .CodeMirror{
        border-top: 1px solid #eee;
        border-bottom: 1px solid #eee;
        line-height: 1.3;
        height: 500px;
    }
    .CodeMirror-linenumbers{
      padding: 0 8px;
    }
    .custom-css p, .custom-css li, .custom-css span{
      color: white;
    }
  </style>
@endpush
@push('style-lib')
    <link rel="stylesheet" href="{{asset('assets/admin/css/codemirror.min.css')}}">
    <link rel="stylesheet" href="{{asset('assets/admin/css/monokai.min.css')}}">
@endpush
@push('script-lib')
    <script src="{{asset('assets/admin/js/codemirror.min.js')}}"></script>
    <script src="{{asset('assets/admin/js/xml.js')}}"></script>
    <script src="{{asset('assets/admin/js/sublime.min.js')}}"></script>
@endpush
@push('script')
<script>
    "use strict";
    var editor = CodeMirror.fromTextArea(document.getElementsByClassName("sitemapEditor")[0], {
        lineNumbers: true,
        mode: "text/xml",
        theme: "monokai",
        keyMap: "sublime",
        showCursorWhenSelecting: true,
    });
</script>
@endpush

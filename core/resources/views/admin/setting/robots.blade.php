@extends('admin.layouts.app')
@section('panel')
    <div class="row mb-none-30">
        <div class="col-lg-12">
            {{-- Default Robots.txt Info --}}
            <div class="card mb-4">
                <div class="card-header">
                    <h6>@lang('Secure Default Robots.txt')</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">
                        @lang('A secure default robots.txt is automatically generated that blocks admin areas, user dashboards, and sensitive endpoints while allowing public content like marketplace listings, blogs, and pages.')
                    </p>
                    <div class="alert alert-info mb-3">
                        <h6 class="alert-heading"><i class="las la-shield-alt"></i> @lang('What is Blocked:')</h6>
                        <ul class="mb-0 small">
                            <li><code>/backoffice/</code> - @lang('Admin panel')</li>
                            <li><code>/user/</code> - @lang('User dashboard and private areas')</li>
                            <li><code>/admin/</code> - @lang('Admin routes')</li>
                            <li><code>/install/</code> - @lang('Installation scripts')</li>
                            <li><code>/cron</code> - @lang('Cron trigger endpoint')</li>
                            <li><code>/ipn/</code> - @lang('Payment IPN endpoints')</li>
                            <li><code>/marketplace/nda/</code> - @lang('NDA documents')</li>
                            <li>@lang('URLs with query parameters and edit/delete/create actions')</li>
                        </ul>
                    </div>
                    <div class="alert alert-success mb-3">
                        <h6 class="alert-heading"><i class="las la-check-circle"></i> @lang('What is Allowed:')</h6>
                        <ul class="mb-0 small">
                            <li><code>/</code> - @lang('Homepage')</li>
                            <li><code>/marketplace/</code> - @lang('Public marketplace pages')</li>
                            <li><code>/blog/</code> - @lang('Blog posts')</li>
                            <li><code>/pages/</code> - @lang('Public CMS pages')</li>
                            <li><code>/tools/</code> - @lang('Public tools')</li>
                            <li><code>/contact</code> - @lang('Contact page')</li>
                            <li><code>/sitemap.xml</code> - @lang('Sitemap')</li>
                        </ul>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        <form action="{{ route('admin.setting.robot.regenerate') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn--primary">
                                <i class="las la-sync"></i> @lang('Regenerate Default Robots.txt')
                            </button>
                        </form>
                        <a href="{{ route('robots') }}" target="_blank" class="btn btn--info">
                            <i class="las la-external-link-alt"></i> @lang('View Live Robots.txt')
                        </a>
                    </div>
                </div>
            </div>

            {{-- Robots.txt Editor --}}
            <div class="card">
                <div class="card-header">
                    <h6>@lang('Robots.txt Editor')</h6>
                    <p class="text-muted mb-0 small">@lang('You can manually edit the robots.txt below. The secure default is automatically generated, but you can customize it if needed.')</p>
                </div>
                <form method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group">
                            <textarea class="form-control" rows="15" name="robots" style="font-family: 'Courier New', monospace; font-size: 13px;">{{ $fileContent ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn--primary w-100 h-45">@lang('Save Robots.txt')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

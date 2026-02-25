@extends('admin.layouts.app')
@section('panel')
<div class="row mb-none-30">
    <div class="col-md-12 mb-30">
        <div class="card bl--5 border--primary">
            <div class="card-body">
                <p class="text--primary">@lang('Paste the full Google AdSense script (e.g. the async script tag from your AdSense account). The script will be output in the frontend head when enabled.')</p>
                <p class="text--warning">@lang('Only add scripts you trust. Invalid or malicious code can affect site behavior.')</p>
            </div>
        </div>
    </div>
    <div class="col-lg-12">
        <div class="card">
            <form method="post" action="{{ route('admin.setting.adsense') }}">
                @csrf
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">@lang('Enable Google AdSense')</label>
                        <input type="checkbox" data-width="100%" data-size="large" data-onstyle="-success" data-offstyle="-danger" data-bs-toggle="toggle" data-height="35" data-on="@lang('Enable')" data-off="@lang('Disable')" name="google_adsense_enable" @if(gs('google_adsense_enable')) checked @endif>
                    </div>
                    <div class="form-group">
                        <label class="form-label">@lang('AdSense Script')</label>
                        <textarea class="form-control font-monospace" name="google_adsense_script" rows="8" placeholder="<script async src=&quot;https://pagead2.googlesyndication.com/...&quot;></script>">{{ old('google_adsense_script', gs('google_adsense_script')) }}</textarea>
                        <small class="text-muted">@lang('Paste the complete script tag(s) from your Google AdSense account.')</small>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn--primary w-100 h-45">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

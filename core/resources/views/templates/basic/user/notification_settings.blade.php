@extends($activeTemplate . 'user.layouts.app')
@section('panel')
    <div class="row">
        <div class="col-12">
            <div class="card custom--card">
                <div class="card-header">
                    <h5 class="card-title mb-0">@lang('Notification Settings')</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">@lang('Choose which notifications you want to receive. By default all are enabled. Turn off any you do not want.')</p>

                    <form action="{{ route('user.notification.settings.update') }}" method="post">
                        @csrf
                        @foreach ($grouped as $category => $items)
                            <div class="notification-category mb-4">
                                <h6 class="border-bottom pb-2 mb-3 text--primary">{{ __($category) }}</h6>
                                <div class="row g-3">
                                    @foreach ($items as $key => $item)
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <div class="form-check form-switch">
                                                <input type="hidden" name="notifications[{{ $key }}]" value="0">
                                                <input class="form-check-input" type="checkbox" name="notifications[{{ $key }}]" value="1" id="notif_{{ $key }}" {{ $item['enabled'] ? 'checked' : '' }}>
                                                <label class="form-check-label" for="notif_{{ $key }}">{{ __($item['label']) }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                        <button type="submit" class="btn btn--base">@lang('Save Settings')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

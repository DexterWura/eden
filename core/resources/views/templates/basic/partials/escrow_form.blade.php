@php
    $categories = App\Models\Category::active()->get();
@endphp
<form action="{{ route('user.escrow.step.one.submit') }}" method="POST">
    @csrf
    <div class="row g-4">
        <div class="col-md-12">
            <div class="input-group h-50 select2-parent">
                <select name="type" class="input-group-text input-group-width bg-white pe-2 form--control select2-basic"
                    data-minimum-results-for-search="-1" required>
                    <option value="">@lang('Select One')</option>
                    <option value="1" selected>@lang('I am Selling')</option>
                    <option value="2">@lang('I am Buying')</option>
                </select>

                <select name="category_id" class="form-select form--control select2-basic" required>
                    <option value="">@lang('Select One')</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ __($category->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-12">
            <div class="input-group">
                <span class="input-group-text input-group-width bg-white">@lang('For the Amount Of')</span>
                <input type="number" step="any" class="form-control form--control" name="amount" required>
                <span class="input-group-text bg-white border-end">{{ __(gs('cur_text')) }}</span>
            </div>
        </div>
    </div>

    <div class="mx-auto mt-4 hero-button">
        <button type="submit" class="btn btn--xl btn--base">@lang('Continue to Next')</button>
    </div>
</form>

@push('style')
    <style>
        .select2-container--default .select2-selection--single {
            border-color: unset !important;
            border-width: 0px !important;
            border-radius: 0 !important;
            height: 100% !important;
            border: unset;
            padding: 0 !important;
        }

        .select2-container--open .select2-selection.select2-selection--single,
        .select2-container--open .select2-selection.select2-selection--multiple {
            border-color: unset !important;
        }

        .input-group {
            flex-wrap: nowrap !important;
        }

        .input-group:has(.select2) {
            border: 1px solid hsl(var(--border)/0.5) !important;
            padding: 1px;
            border-radius: 6px;
        }

        .input-group .select2-container:first-of-type .select2-selection__rendered {
            border-right: 1px solid hsl(var(--border)/0.5) !important;
            margin-right: 1px;
        }

        .select2-container--open .select2-selection.select2-selection--single,
        .select2-container--open .select2-selection.select2-selection--multiple {
            border-radius: 0 6px 6px 0 !important;
        }

        .input-group .select2-container:first-of-type .select2-selection--single {
            border-radius: 6px 0 0 6px !important;
            max-width: 160px;
        }

        .input-group .select2-container:last-of-type .select2-selection--single {
            border-radius: 0 6px 6px 0 !important;
        }
    </style>
@endpush

@push('style-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/select2.min.css') }}">
@endpush

@push('script-lib')
    <script src="{{ asset('assets/global/js/select2.min.js') }}"></script>
@endpush

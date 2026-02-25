@extends($activeTemplate . 'layouts.app')
@section('panel')
    <section class="maintenance-page d-flex align-items-center justify-content-center py-5">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-7 text-center">
                    <div class="row justify-content-center">
                        <div class="col-xl-10">
                            <h4 class="text--danger">{{ __(@$maintenance->data_values->heading) }}</h4>
                        </div>
                        <div class="col-sm-6 col-8 col-lg-12">
                            <img src="{{ getImage(getFilePath('maintenance') . '/' . @$maintenance->data_values->image, getFileSize('maintenance')) }}"
                                alt="@lang('image')" class="img-fluid mx-auto mb-5">
                        </div>
                    </div>
                    <p class="mx-auto text-center">@php echo $maintenance->data_values->description @endphp</p>
                </div>
            </div>
        </div>
    </section>
@endsection



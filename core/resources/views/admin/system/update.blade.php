@extends('admin.layouts.app')
@section('panel')
    <div class="row gy-4">
        <div class="col-lg-12">
            <div class="d-flex justify-content-center">
                <div class="update-available-card-wrapper">
                    <div class="update-available-card border--success">
                        <div class="update-available-card-top">
                            <div class="update-available-card__item w-100">
                                <h4 class="text--success">{{ systemDetails()['version'] }}</h4>
                                <p class="text--success">@lang('Current Version')</p>
                            </div>
                            {{-- Latest version display removed - display current version only --}}
                        </div>
                        <div class="update-available-card-bottom">
                            <p><span><i class="las la-info-circle text--primary"></i></span> <span><strong>@lang('Current System Version')</strong> - @lang('This page displays the current version of the system.')</span></p>
                        </div>
                    </div>
                    {{-- Update functionality removed - display only --}}
                </div>
            </div>
        </div>
    </div>

    {{-- Update modal removed - functionality disabled --}}

@endsection

{{-- Update functionality removed - display only page --}}


@push('style')
    <style>
        .spinner-border {
            width: 100px;
            height: 100px;
        }

        .update-available-card {
            background-color: #fff;
            border-radius: 5px;
            padding: 30px;
            border: 3px solid;
        }

        .update-available-card-wrapper {
            width: 730px;
        }

        .update-available-card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .update-available-card__item {
            text-align: center;
            width: 50%;
        }

        .update-available-card__item:not(:first-child) {
            border-left: 2px solid #e5e5e5;
        }

        .update-available-card__item h4 {
            margin-bottom: 0px;
            font-size: 6.25rem;
            font-weight: 800;
            line-height: 1;
        }

        .update-available-card__item p {
            font-size: 1.875rem;
            font-weight: 500;
        }

        .update-available-card-bottom p {
            display: flex;
            gap: 10px;
            line-height: 1.4;
            font-style: italic;
        }

        .update-available-card-bottom p i {
            font-size: 1.25rem;
            vertical-align: middle;
        }


        @media only screen and (max-width: 767px) {
            .update-available-card__item h4 {
                font-size: 4.688rem;
            }

            .update-available-card__item p {
                font-size: 1.5rem;
            }
        }

        @media only screen and (max-width: 575px) {
            .update-available-card__item h4 {
                font-size: 3rem;
                line-height: 1.4;
            }

            .update-available-card__item p {
                font-size: 1.125rem;
            }

            .update-available-card {
                padding: 30px 15px;
            }

            .update-available-card-top {
                margin-bottom: 20px;
            }

            .update-available-card__item:not(:first-child) {
                border-width: 1px;
            }
        }

        @media only screen and (max-width: 375px) {
            .update-available-card__item h4 {
                font-size: 2rem;
            }

            .update-available-card__item p {
                font-size: 0.875rem;
            }

            .update-available-card-top {
                margin-bottom: 20px;
            }
        }
        .modal-version-text{
            font-size: 70px;
            font-weight: 600;
            margin-top: 10px;
        }
        .card-section__icon {
            text-align: center;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid #28c76f;
            margin: 0 auto;
            margin-bottom: 20px;
        }

        .card-section__icon i {
            font-size: 50px;
            color: #28c76f;
        }
    </style>
@endpush

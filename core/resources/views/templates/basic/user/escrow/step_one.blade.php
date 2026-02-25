@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <section class="section bg--light">
        <div class="container">
            <div class="row justify-content-center gy-4">
                <div class="col-md-10 col-lg-8 col-xl-6">
                    <div class="card custom--card">
                        <div class="card-body">
                            @include($activeTemplate . 'partials.escrow_form')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('style')
    <style>
        .btn--base {
            width: 100%;
        }

        @media(max-width:480px) {
            .custom--card .card-body {
                padding: .8rem;
            }
        }

        @media(max-width:360px) {
            .custom--card .card-body {
                padding: .5rem;
            }

            .input-group-text {
                font-size: 12px;
            }
        }
    </style>
@endpush

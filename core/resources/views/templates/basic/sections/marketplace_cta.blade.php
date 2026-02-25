@php
    $content = getContent('marketplace_cta.content', true);
    if(!@$content->data_values->status) return;
@endphp

<section class="marketplace-cta py-5 bg--base">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-8">
                <h3 class="text-white mb-3">
                    {{ __(@$content->data_values->heading) }}
                </h3>
                <p class="text-white-50 mb-4">
                    {{ __(@$content->data_values->subheading) }}
                </p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="{{ @$content->data_values->button_url }}" class="btn btn-light btn-lg rounded-pill px-5">
                        <i class="las la-rocket"></i> {{ __(@$content->data_values->button_text) }}
                    </a>
                    <a href="{{ route('marketplace.browse') }}" class="btn btn-outline-light btn-lg rounded-pill px-5">
                        <i class="las la-search"></i> @lang('Browse Listings')
                    </a>
                </div>
                
                <div class="cta-features mt-5 d-flex justify-content-center gap-4 flex-wrap">
                    <div class="cta-feature text-white">
                        <i class="las la-check-circle me-2"></i>
                        @lang('Free to List')
                    </div>
                    <div class="cta-feature text-white">
                        <i class="las la-shield-alt me-2"></i>
                        @lang('Secure Escrow')
                    </div>
                    <div class="cta-feature text-white">
                        <i class="las la-users me-2"></i>
                        @lang('Thousands of Buyers')
                    </div>
                    <div class="cta-feature text-white">
                        <i class="las la-headset me-2"></i>
                        @lang('24/7 Support')
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


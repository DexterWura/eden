@php
    $faqContent = getContent('faq.content', true);
    $faqElements = getContent('faq.element', false, null, true);
@endphp

<section class="section">
    <div class="container">
        <div class="row g-4 justify-content-xl-between align-items-center">
            <div class="col-lg-6">
                <img src="{{frontendImage('faq' , @$faqContent->data_values->image, '645x580') }}" alt="@lang('image')" class="img-fluid" />
            </div>
            <div class="col-lg-6">
                <div class="ms-xxl-5">
                    <h3 class="section-title mt-lg-0 text-center text-lg-start">
                        {{ __(@$faqContent->data_values->heading) }}
                    </h3>
                    <p class="section-subtitle fw-light text-center text-lg-start">
                        {{ __(@$faqContent->data_values->subheading) }}
                    </p>
                    <div class="accordion custom--accordion" id="accordionExample">
                        @foreach ($faqElements as $faq)
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button collapsed @if (!$loop->first) show @endif" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $loop->index }}" @if ($loop->first) aria-expanded="true" @endif>
                                        {{ __(@$faq->data_values->question) }}
                                    </button>
                                </h2>
                                <div id="collapse-{{ $loop->index }}" class="accordion-collapse collapse @if ($loop->first) show @endif" data-bs-parent="#accordionExample">
                                    <div class="accordion-body">
                                        @php echo @$faq->data_values->answer @endphp
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

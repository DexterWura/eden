@php
    $blogContent = getContent('blog.content', true);
    $blogElements = getContent('blog.element', false, 3, false);
@endphp

<section class="section blog-section bg--light">
    <div class="section__head">
        <div class="container">
            <div class="row g-4 justify-content-center">
                <div class="col-md-8 col-lg-7 col-xl-6">
                    <h3 class="section-title mt-0 text-center">
                        {{ __(@$blogContent->data_values->heading) }}
                    </h3>
                    <p class="section-subtitle mb-0 text-center mx-auto">
                        {{ __(@$blogContent->data_values->subheading) }}
                    </p>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row g-4 g-lg-3 g-xxl-4 justify-content-center">
            @foreach ($blogElements as $blog)
                <div class="col-md-6 col-lg-4">
                    <div class="blog-post">
                        <a href="{{ route('blog.details', $blog->slug) }}" class="blog-post__img t-link">
                            <img src="{{ frontendImage('blog', 'thumb_' . @$blog->data_values->image, '440x240') }}" alt="@lang('image')"
                                class="blog-post__img-is">
                        </a>
                        <div class="blog-post__body">
                            <h5 class="mt-0">
                                <a href="{{ route('blog.details', $blog->slug) }}" class="t-link blog-post__title">
                                    {{ strLimit(__(@$blog->data_values->title), 60) }}
                                </a>
                            </h5>
                            <p>
                                {{ strLimit(__(strip_tags(@$blog->data_values->description)), 80) }}
                            </p>
                            <a class="read-more" href="{{ route('blog.details', $blog->slug) }}">@lang('Read More')
                                <i class="las la-arrow-right"></i></a>
                            <ul class="list list--row">
                                <li class="list__item">
                                    <div class="d-flex align-items-center">
                                        <span class="flex-shrink-0 text--base d-inline-block lg-text me-2">
                                            <i class="fas fa-calendar-alt"></i>
                                        </span>
                                        <span class="d-block sm-text">
                                            {{ showDateTime(@$blog->created_at, 'd F Y') }}
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>

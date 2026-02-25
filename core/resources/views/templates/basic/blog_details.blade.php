@extends($activeTemplate . 'layouts.frontend')
@section('content')
    <div class="section">
        <div class="container">
            <div class="row gy-4 justify-content-center">
                <div class="col-lg-8">
                    <div class="blog-details__img">
                        <img src="{{ getImage('assets/images/frontend/blog/' . @$blog->data_values->image, '425x300') }}" alt="@lang('image')" />
                    </div>
                    <h5>
                        {{ __(@$blog->data_values->title) }}
                    </h5>
                    <div class="mt-4">
                        @php echo @$blog->data_values->description; @endphp
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 mt-5">
                        <h5 class="m-0">@lang('Share On')</h5>
                        <div class="d-flex gap-2">
                            <a href="http://www.facebook.com/sharer.php?u={{ urlencode(url()->current()) }}&p[title]={{ slug(@$blog->data_values->title) }}"
                                class="share--icon" target="_blank" title="@lang('Facebook')">
                                <i class="lab la-facebook-f"></i>
                            </a>
                            <a href="http://twitter.com/share?text={{ slug(@$blog->data_values->title) }}&url={{ urlencode(url()->current()) }}"
                                class="share--icon" target="_blank" title="@lang('Twitter')">
                                <i class="lab la-twitter"></i>
                            </a>
                            <a href="http://pinterest.com/pin/create/button/?url={{ urlencode(url()->current()) }}&description={{ slug(@$blog->data_values->title) }}"
                                class="share--icon" target="_blank" title="@lang('Pinterest')">
                                <i class="lab la-pinterest-p"></i>
                            </a>
                            <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url()->current()) }}&title={{ slug(@$blog->data_values->title) }}"
                                class="share--icon" target="_blank" title="@lang('Linkedin')">
                                <i class="lab la-linkedin-in"></i>
                            </a>
                        </div>
                    </div>
                    <div class="mt-4">
                        <div class="fb-comments" data-href="{{ url()->current() }}" data-numposts="5">
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="blog-sidebar">
                        <h4 class="title">@lang('Recent Blogs')
                            <hr>
                        </h4>
                        <ul class="list list--column ">
                            @foreach ($recentBlogs as $rBlog)
                                <li class="list--column__item">
                                    <div class="d-flex pb-3">
                                        <div class="me-3 flex-shrink-0">
                                            <div class="user__img user__img--md">
                                                <img src="{{ frontendImage('blog', 'thumb_' . @$rBlog->data_values->image, '440x240') }}" alt="image"
                                                    class="user__img-is" />
                                            </div>
                                        </div>
                                        <div class="article">
                                            <h6 class="blog-post__title fw-md m-0">
                                                <a href="{{ route('blog.details', $rBlog->slug) }}" class="t-link d-block text--accent t-link--base">
                                                    {{ strLimit(__(@$rBlog->data_values->title), 80) }}
                                                </a>
                                            </h6>

                                            <span class="small text-muted">
                                                {{ showDateTime(@$blog->created_at, 'F d, Y') }}
                                            </span>

                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('fbComment')
    @php echo loadExtension('fb-comment') @endphp
@endpush

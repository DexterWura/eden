<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ gs()->siteName($pageTitle ?? '429 | Too Many Requests') }}</title>
    <link rel="shortcut icon" type="image/png" href="{{ siteFavicon() }}">
    <!-- bootstrap 4  -->
    <link rel="stylesheet" href="{{ asset('assets/global/css/bootstrap.min.css') }}">
    <!-- dashdoard main css -->
    <link rel="stylesheet" href="{{ asset('assets/errors/css/main.css') }}">
</head>

<body>
    <!-- error-429 start -->
    <div class="error error-429">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-7 text-center">
                    <img src="{{ asset('assets/errors/images/error-419.png') }}" alt="image">

                    <h2 class="title"> @lang('Too Many Requests')</h2>
                    <p class="description">{{ $message ?? 'You have made too many requests. Please wait a moment and try again.' }}</p>
                    @if(isset($retry_after))
                        <p class="description">@lang('Please try again in') {{ \Carbon\Carbon::createFromTimestamp($retry_after)->diffForHumans() }}</p>
                    @endif
                    <a href="{{ url()->previous() }}" class="cmn-btn mt-4"><span class="icon">
                            <svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12.5 0C5.59644 0 0 5.59644 0 12.5C0 19.4036 5.59644 25 12.5 25C19.4036 25 25 19.4036 25 12.5C25 5.59644 19.4036 0 12.5 0ZM18.75 13.75H11.25V18.75C11.25 19.3023 10.8023 19.75 10.25 19.75C9.69772 19.75 9.25 19.3023 9.25 18.75V12.5C9.25 11.9477 9.69772 11.5 10.25 11.5H18.75C19.3023 11.5 19.75 11.9477 19.75 12.5C19.75 13.0523 19.3023 13.75 18.75 13.75Z" />
                            </svg>
                        </span><span class="text"> @lang('Try Again')</span></a>
                </div>
            </div>
        </div>
    </div>
    <!-- error-429 end -->
</body>

</html>

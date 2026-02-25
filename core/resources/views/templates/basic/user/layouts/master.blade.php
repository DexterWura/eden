<!-- meta tags and other links -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ gs()->siteName($pageTitle ?? '') }}</title>

    <link rel="shortcut icon" type="image/png" href="{{siteFavicon()}}">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet" crossorigin="anonymous">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/line-awesome/1.3.0/line-awesome/css/line-awesome.min.css" rel="stylesheet" crossorigin="anonymous">

    @stack('style-lib')

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" crossorigin="anonymous">
    @if(function_exists('gs') && gs('base_color'))
    <link rel="stylesheet"
        href="{{ asset($activeTemplateTrue . 'css/color.php') }}?color={{ gs('base_color') }}&secondColor={{ gs('secondary_color') }}">
    @endif

    @stack('style')
</head>
<body>
@yield('content')

<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

@include('partials.notify')
@stack('script-lib')

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" crossorigin="anonymous"></script>

{{-- Breadcrumb and sidebar toggle (always) --}}
<script>
    (function($){
        $('.breadcrumb-nav-open').on('click', function() {
            $(this).toggleClass('active');
            $('.breadcrumb-nav').toggleClass('active');
        });
        $('.breadcrumb-nav-close').on('click', function() {
            $('.breadcrumb-nav').removeClass('active');
        });
        if($('.topTap').length){
            $('.breadcrumb-nav-open').removeClass('d-none');
        }
    })(jQuery);
</script>

@stack('script')

</body>
</html>


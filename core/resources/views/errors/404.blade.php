@php
    $debug = config('app.debug');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found – Eden</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:600px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}
        h1{color:#6CAA64;}
        .error-box{background:#fff;border:1px solid #ddd;border-radius:8px;padding:1rem;margin:1rem 0;font-size:0.9rem;}
        a{color:#6CAA64;}
    </style>
</head>
<body>
    <h1>Page not found</h1>
    <p>The page you're looking for doesn't exist or has been moved.</p>
    @if($debug && isset($exception))
        <div class="error-box"><strong>Debug:</strong> {{ e($exception->getMessage()) }}</div>
    @endif
    <p><a href="{{ url('/') }}">Return to home</a></p>
</body>
</html>

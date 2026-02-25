@php
    $debug = config('app.debug');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access denied – Eden</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:600px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}
        h1{color:#6CAA64;}
        .error-box{background:#fff;border:1px solid #ddd;border-radius:8px;padding:1rem;margin:1rem 0;}
        a{color:#6CAA64;}
    </style>
</head>
<body>
    <h1>Access denied</h1>
    <p>You don't have permission to view this page.</p>
    @if($debug && isset($exception))
        <div class="error-box"><strong>Debug:</strong> {{ e($exception->getMessage()) }}</div>
    @endif
    <p><a href="{{ url('/') }}">Return to home</a></p>
</body>
</html>

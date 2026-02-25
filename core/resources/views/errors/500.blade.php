@php
    $debug = config('app.debug');
    $message = isset($exception) ? ($exception->getMessage() ?? 'Server error') : 'Server error';
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error – Eden</title>
    <style>
        body{font-family:system-ui,sans-serif;max-width:600px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}
        h1{color:#6CAA64;}
        .error-box{background:#fff;border:1px solid #ddd;border-radius:8px;padding:1rem;margin:1rem 0;}
        .error-box pre{overflow:auto;font-size:0.85rem;white-space:pre-wrap;word-break:break-word;}
        a{color:#6CAA64;}
    </style>
</head>
<body>
    <h1>Something went wrong</h1>
    <p>We're sorry. The server encountered an error. Please try again later or contact the site owner.</p>
    @if($debug && isset($exception))
        <div class="error-box">
            <strong>Debug (APP_DEBUG=true)</strong>
            <p><strong>{{ get_class($exception) }}</strong>: {{ e($message) }}</p>
            @if($exception->getFile())
                <p>In <code>{{ e($exception->getFile()) }}</code> on line {{ $exception->getLine() }}</p>
            @endif
            @if($exception->getTraceAsString())
                <pre>{{ e($exception->getTraceAsString()) }}</pre>
            @endif
        </div>
    @endif
    <p><a href="{{ url('/') }}">Return to home</a></p>
</body>
</html>

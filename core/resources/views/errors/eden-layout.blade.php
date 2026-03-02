<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{{ $title ?? 'Error' }} — Eden</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
  @php $cssPath = public_path('css/main.css'); $cssV = file_exists($cssPath) ? substr(md5_file($cssPath), 0, 12) : ''; @endphp
  <link rel="stylesheet" href="{{ asset('css/main.css') }}{{ $cssV ? '?v=' . $cssV : '' }}">
  <style>
    .error-page { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 40px 20px; text-align: center; }
    .error-page-inner { max-width: 480px; }
    .error-page-code { font-size: 6rem; font-weight: 700; line-height: 1; color: var(--accent); letter-spacing: -0.04em; margin-bottom: 16px; }
    .error-page-title { font-size: 1.5rem; font-weight: 600; margin-bottom: 12px; color: var(--text); }
    .error-page-desc { color: var(--text-muted); font-size: 1rem; margin-bottom: 28px; line-height: 1.6; }
    .error-page .btn { display: inline-block; padding: 12px 24px; border-radius: var(--radius-sm); font-weight: 600; text-decoration: none; transition: opacity 0.2s; }
    .error-page .btn-primary { background: var(--accent); color: var(--bg); }
    .error-page .btn-primary:hover { opacity: 0.95; }
  </style>
</head>
<body>
  <div class="bg-grid"></div>
  <div class="bg-glow"></div>
  <div class="error-page">
    <div class="error-page-inner">
      @yield('content')
    </div>
  </div>
</body>
</html>

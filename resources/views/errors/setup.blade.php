<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Setup required – Eden</title>
    <style>body{font-family:system-ui,sans-serif;max-width:560px;margin:3rem auto;padding:0 1rem;background:#f5f0e1;}h1{color:#6CAA64;}a{color:#6CAA64;}code{background:#eee;padding:.2em .4em;border-radius:4px;}pre{overflow:auto;background:#eee;padding:1rem;border-radius:4px;}</style>
</head>
<body>
    <h1>Setup required</h1>
    <p>Eden is not configured yet. Create your <code>.env</code> file and generate an application key:</p>
    <pre>cp .env.example .env
php artisan key:generate</pre>
    <p>If you have SSH access, run the commands above in the project root. Then open <strong>/install</strong> in your browser to complete the web installer.</p>
    <p>If you cannot run commands, ensure <code>composer.phar</code> is in the project root and reload the site to auto-install, or ask your host to run <code>php composer.phar install --no-dev</code>, then visit <a href="/install">/install</a>.</p>
</body>
</html>

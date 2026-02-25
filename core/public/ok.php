<?php
// If https://yoursite.com/ok.php returns "OK" (200), PHP runs and doc root is core/public. Remove after use.
header('HTTP/1.1 200 OK');
header('Content-Type: text/plain; charset=utf-8');
echo 'OK';
exit;

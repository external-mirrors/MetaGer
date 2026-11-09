<?php

// Upstream image host for PictureproxyTest, served by `php -S`.
// GET /png?bytes=N answers a valid PNG padded to exactly N bytes;
// add &length=0 to stream it chunked, without a Content-Length.
// GET /avif-labelled-jpeg answers an AVIF (800 px wide) as image/jpeg, the
// way an upstream that negotiates formats on its own can label it.
// GET /truncated-jpeg answers the first bytes of a JPEG as image/jpeg.

$path  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$bytes = (int) ($_GET['bytes'] ?? 0);

if ($path === '/avif-labelled-jpeg') {
    header('Content-Type: image/jpeg');
    readfile(__DIR__ . '/image.avif');
    return true;
}

if ($path === '/truncated-jpeg') {
    header('Content-Type: image/jpeg');
    echo "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x00\x00\x01\x00\x01\x00\x00";
    return true;
}

if ($path !== '/png') {
    http_response_code(404);
    return true;
}

// A 1x1 transparent PNG; trailing bytes after IEND are ignored by readers.
$png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==');
$pad = max(0, $bytes - strlen($png));

header('Content-Type: image/png');
if (($_GET['length'] ?? '1') !== '0') {
    header('Content-Length: ' . (strlen($png) + $pad));
}
echo $png;
while ($pad > 0) {
    $chunk = min($pad, 1 << 20);
    echo str_repeat("\0", $chunk);
    flush();
    $pad -= $chunk;
}

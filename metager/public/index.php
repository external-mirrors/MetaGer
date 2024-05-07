<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

# Manchmal passiert es, dass ein Proxy sowohl den HEADER HTTP_FORWARDED, als auch den HEADER "HTTP_X_FORWARDED_FOR" setzt
# Wir löschen den einen und verwenden Ihn nicht:
if (isset($_SERVER["HTTP_FORWARDED"]) && isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
    unset($_SERVER["HTTP_FORWARDED"]);
}

$_SERVER["AGENT"] = "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:40.0) Gecko/20100101 Firefox/40.1";
if (!empty($_SERVER["HTTP_USER_AGENT"])) {
    $_SERVER["AGENT"] = $_SERVER["HTTP_USER_AGENT"];
}

// Register the Composer autoloader...
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
(require_once __DIR__ . '/../bootstrap/app.php')
    ->handleRequest(Request::capture());
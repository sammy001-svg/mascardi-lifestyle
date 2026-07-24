<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Logger;
use App\Core\Session;
use App\Core\View;

// Web-root location. Each front controller defines this from its own real
// directory before requiring bootstrap, so it stays correct whether the web
// root is named public/ (local dev) or public_html (cPanel). This fallback
// only applies to CLI scripts/tools that bypass a front controller.
if (!defined('PUBLIC_PATH')) {
    define('PUBLIC_PATH', dirname(__DIR__) . '/public');
}

// --- Simple PSR-4-ish autoloader for the App\ namespace (no Composer needed at runtime) ---
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = __DIR__ . '/' . str_replace('\\', '/', $relative) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

require __DIR__ . '/helpers.php';

// --- Config-driven error display: never leak stack traces in production ---
$config = Config::get();
if (Config::isProduction()) {
    ini_set('display_errors', '0');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
ini_set('log_errors', '1');
ini_set('error_log', dirname(__DIR__) . '/storage/logs/app.log');

set_exception_handler(function (\Throwable $e): void {
    Logger::error($e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    http_response_code(500);
    if (Config::isProduction()) {
        echo 'Something went wrong. Please try again shortly.';
    } else {
        echo '<pre>' . htmlspecialchars((string) $e, ENT_QUOTES, 'UTF-8') . '</pre>';
    }
});

date_default_timezone_set('Africa/Nairobi');

Session::start();
View::init(dirname(__DIR__) . '/resources/views');

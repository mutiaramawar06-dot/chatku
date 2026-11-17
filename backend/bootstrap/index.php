<?php

declare(strict_types=1);

require_once __DIR__ . '/loadEnv.php';
require_once __DIR__ . '/response.php';

use Lib\Router;

$envPath = dirname(__DIR__) . '/.env';
if (is_file($envPath)) {
    \Lib\loadEnv($envPath);
}

// Global exception and error handlers to prevent exposing internal errors or stack traces (FR-19, Section 6)
set_exception_handler(static function (\Throwable $e): void {
    error_log((string) $e);
    \Lib\responseJson(
        statusCode: 500,
        message: 'Internal server error',
        errors: ['server' => 'An unexpected error occurred. Please try again later.']
    );
});

set_error_handler(static function (int $errno, string $errstr, string $errfile, int $errline): bool {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    error_log("PHP Error [$errno]: $errstr in $errfile on line $errline");
    return true;
});

// PSR-4 Autoloader
spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'Controllers\\' => dirname(__DIR__) . '/Controllers/',
        'Models\\'      => dirname(__DIR__) . '/models/',
        'Lib\\'         => dirname(__DIR__) . '/lib/',
        'Services\\'    => dirname(__DIR__) . '/Services/',
        'Middleware\\'  => dirname(__DIR__) . '/Middleware/',
        'Helpers\\'     => dirname(__DIR__) . '/Helpers/',
        'Firebase\\JWT\\' => dirname(__DIR__) . '/vendor/php-jwt/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (strncmp($class, $prefix, strlen($prefix)) !== 0) {
            continue;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $directory . str_replace('\\', '/', $relativeClass) . '.php';

        if (is_file($file)) {
            require $file;
            return;
        }
    }
});

// Handle CORS
\Middleware\CorsMiddleware::handle();

$router = new Router();
$registerRoutes = require dirname(__DIR__) . '/routes/api.php';
$registerRoutes($router);

return $router;

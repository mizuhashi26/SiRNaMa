<?php

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Setup writable directories in /tmp for Vercel Serverless read-only environment
$storageTmpPath = '/tmp/storage';
$directories = [
    $storageTmpPath,
    $storageTmpPath . '/framework',
    $storageTmpPath . '/framework/views',
    $storageTmpPath . '/framework/cache',
    $storageTmpPath . '/framework/sessions',
    $storageTmpPath . '/logs',
    $storageTmpPath . '/app',
];

foreach ($directories as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Redirect view compiled path and cache path to /tmp if not explicitly defined
if (! getenv('VIEW_COMPILED_PATH')) {
    putenv('VIEW_COMPILED_PATH=' . $storageTmpPath . '/framework/views');
}

// Maintenance mode check
if (file_exists($maintenance = __DIR__ . '/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register Composer Autoloader
require __DIR__ . '/../vendor/autoload.php';

// Bootstrap Laravel and handle request
$app = require_once __DIR__ . '/../bootstrap/app.php';

// Prevent Symfony/Laravel from treating "/api" as a subdirectory baseUrl
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';

// Bind storage path to /tmp for runtime writes
$app->useStoragePath($storageTmpPath);

$app->handleRequest(Request::capture());


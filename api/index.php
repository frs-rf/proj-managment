<?php

// Setup /tmp storage for Vercel Serverless environment
$tmpStorage = '/tmp/storage';
if (!is_dir($tmpStorage)) {
    mkdir($tmpStorage.'/app', 0777, true);
    mkdir($tmpStorage.'/framework/cache/data', 0777, true);
    mkdir($tmpStorage.'/framework/sessions', 0777, true);
    mkdir($tmpStorage.'/framework/views', 0777, true);
    mkdir($tmpStorage.'/logs', 0777, true);
}

// Override APP_STORAGE environment variable
$_ENV['APP_STORAGE'] = $tmpStorage;
putenv('APP_STORAGE=' . $tmpStorage);

// Override bootstrap cache to /tmp to prevent read-only filesystem errors
$_ENV['APP_SERVICES_CACHE'] = '/tmp/storage/framework/cache/services.php';
$_ENV['APP_PACKAGES_CACHE'] = '/tmp/storage/framework/cache/packages.php';
$_ENV['APP_CONFIG_CACHE'] = '/tmp/storage/framework/cache/config.php';
$_ENV['APP_ROUTES_CACHE'] = '/tmp/storage/framework/cache/routes.php';
$_ENV['APP_EVENTS_CACHE'] = '/tmp/storage/framework/cache/events.php';
putenv('APP_SERVICES_CACHE=' . $_ENV['APP_SERVICES_CACHE']);
putenv('APP_PACKAGES_CACHE=' . $_ENV['APP_PACKAGES_CACHE']);
putenv('APP_CONFIG_CACHE=' . $_ENV['APP_CONFIG_CACHE']);
putenv('APP_ROUTES_CACHE=' . $_ENV['APP_ROUTES_CACHE']);
putenv('APP_EVENTS_CACHE=' . $_ENV['APP_EVENTS_CACHE']);

require __DIR__ . '/../public/index.php';

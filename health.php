<?php
// Simple health check for debugging Nixpacks deployment
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
    'current_domain' => $_SERVER['HTTP_HOST'] ?? 'unknown',
    'request_uri' => $_SERVER['REQUEST_URI'] ?? 'unknown',
    'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown',
    'script_name' => $_SERVER['SCRIPT_NAME'] ?? 'unknown',
    'app_path' => __DIR__,
    'writable_storage' => is_writable(__DIR__ . '/storage') ? 'yes' : 'no',
    'writable_bootstrap' => is_writable(__DIR__ . '/bootstrap/cache') ? 'yes' : 'no',
    'app_url_env' => $_ENV['APP_URL'] ?? 'not set',
    'coolify_fqdn' => $_ENV['COOLIFY_FQDN'] ?? 'not set'
];

echo json_encode($health, JSON_PRETTY_PRINT);
?>

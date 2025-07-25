<?php
// Simple health check that doesn't depend on Storage Boxx framework
header('Content-Type: application/json');

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'server' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown'
];

// Basic environment check
$env_check = [
    'APP_URL' => !empty($_ENV['APP_URL']) ? 'set' : 'missing',
    'DB_HOST' => !empty($_ENV['DB_HOST']) ? 'set' : 'missing',
    'DB_PASSWORD' => !empty($_ENV['DB_PASSWORD']) ? 'set' : 'missing'
];

$health['environment'] = $env_check;

// Count missing critical vars
$missing = array_filter($env_check, function($v) { return $v === 'missing'; });
if (count($missing) > 0) {
    $health['status'] = 'warning';
    $health['missing_vars'] = array_keys($missing);
}

http_response_code(200);
echo json_encode($health, JSON_PRETTY_PRINT);
?>
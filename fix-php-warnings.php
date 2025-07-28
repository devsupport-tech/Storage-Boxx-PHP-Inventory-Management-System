<?php
// Quick fix for PHP warnings in production
// Add this to your environment variables in Coolify

// Disable PHP warnings and notices in production
if (getenv('APP_ENV') === 'production' || $_ENV['APP_ENV'] === 'production') {
    error_reporting(E_ERROR | E_PARSE);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

echo json_encode([
    'status' => 'PHP warnings disabled for production',
    'error_reporting' => error_reporting(),
    'display_errors' => ini_get('display_errors'),
    'log_errors' => ini_get('log_errors'),
    'app_env' => getenv('APP_ENV') ?: $_ENV['APP_ENV'] ?? 'not_set'
], JSON_PRETTY_PRINT);
?>

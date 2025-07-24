<?php
// Enhanced health check for Storage Boxx deployment monitoring
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Load environment if available
if (file_exists(__DIR__ . '/lib/CORE-Env.php')) {
    require_once __DIR__ . '/lib/CORE-Env.php';
    CoreEnv::load();
}

function checkDatabaseConnection($type = 'mysql') {
    try {
        if ($type === 'mysql') {
            $host = $_ENV['DB_HOST'] ?? 'mysql';
            $dbname = $_ENV['DB_NAME'] ?? 'storageboxx';
            $username = $_ENV['DB_USER'] ?? 'storage';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
            $pdo->query('SELECT 1');
            return 'connected';
        } elseif ($type === 'postgres') {
            $host = $_ENV['POSTGRES_HOST'] ?? 'postgres';
            $dbname = $_ENV['POSTGRES_DB'] ?? 'storageboxx';
            $username = $_ENV['POSTGRES_USER'] ?? 'postgres';
            $password = $_ENV['POSTGRES_PASSWORD'] ?? '';
            
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
            $pdo->query('SELECT 1');
            return 'connected';
        } elseif ($type === 'redis') {
            $host = $_ENV['REDIS_HOST'] ?? 'redis';
            $port = $_ENV['REDIS_PORT'] ?? 6379;
            $password = $_ENV['REDIS_PASSWORD'] ?? '';
            
            $redis = new Redis();
            $redis->connect($host, $port);
            if ($password) {
                $redis->auth($password);
            }
            $redis->ping();
            return 'connected';
        }
    } catch (Exception $e) {
        return 'failed: ' . $e->getMessage();
    }
    return 'unknown';
}

function checkRequiredExtensions() {
    $required = ['pdo', 'pdo_mysql', 'openssl', 'mbstring', 'zip', 'gd'];
    $missing = [];
    foreach ($required as $ext) {
        if (!extension_loaded($ext)) {
            $missing[] = $ext;
        }
    }
    return empty($missing) ? 'all_loaded' : 'missing: ' . implode(', ', $missing);
}

$health = [
    'status' => 'ok',
    'timestamp' => date('Y-m-d H:i:s'),
    'environment' => $_ENV['APP_ENV'] ?? 'unknown',
    'version' => [
        'php' => PHP_VERSION,
        'app' => '2.0.0-docker'
    ],
    'server' => [
        'software' => $_SERVER['SERVER_SOFTWARE'] ?? 'unknown',
        'host' => $_SERVER['HTTP_HOST'] ?? 'unknown',
        'document_root' => $_SERVER['DOCUMENT_ROOT'] ?? 'unknown'
    ],
    'storage' => [
        'cache' => is_writable(__DIR__ . '/storage/cache') ? 'writable' : 'not_writable',
        'logs' => is_writable(__DIR__ . '/storage/logs') ? 'writable' : 'not_writable',
        'sessions' => is_writable(__DIR__ . '/storage/sessions') ? 'writable' : 'not_writable',
        'uploads' => is_writable(__DIR__ . '/storage/uploads') ? 'writable' : 'not_writable'
    ],
    'database' => [
        'mysql' => checkDatabaseConnection('mysql'),
        'postgres' => checkDatabaseConnection('postgres'),
        'redis' => class_exists('Redis') ? checkDatabaseConnection('redis') : 'redis_extension_missing'
    ],
    'php_extensions' => checkRequiredExtensions(),
    'configuration' => [
        'app_url' => $_ENV['APP_URL'] ?? 'not_set',
        'jwt_configured' => !empty($_ENV['JWT_SECRET']) ? 'yes' : 'no',
        'supabase_configured' => !empty($_ENV['SUPABASE_URL']) ? 'yes' : 'no'
    ]
];

// Set overall status based on critical checks
$critical_issues = [];
if (strpos($health['storage']['cache'], 'not_writable') !== false) $critical_issues[] = 'storage_cache';
if (strpos($health['storage']['logs'], 'not_writable') !== false) $critical_issues[] = 'storage_logs';
if (strpos($health['php_extensions'], 'missing') !== false) $critical_issues[] = 'php_extensions';

if (!empty($critical_issues)) {
    $health['status'] = 'degraded';
    $health['issues'] = $critical_issues;
    http_response_code(503);
}

echo json_encode($health, JSON_PRETTY_PRINT);
?>

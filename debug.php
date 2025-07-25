<?php
// Debug endpoint to check what's wrong
header('Content-Type: application/json');

$debug = [
    'status' => 'debug',
    'timestamp' => date('Y-m-d H:i:s'),
    'php_version' => PHP_VERSION,
    'environment_check' => [],
    'file_check' => [],
    'extension_check' => [],
    'database_check' => []
];

// Check critical environment variables
$env_vars = ['APP_URL', 'DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASSWORD', 'JWT_SECRET'];
foreach ($env_vars as $var) {
    $debug['environment_check'][$var] = isset($_ENV[$var]) ? 'set' : 'missing';
}

// Check critical files
$files = [
    'lib/CORE-Env.php' => file_exists(__DIR__ . '/lib/CORE-Env.php'),
    'lib/CORE-Config.php' => file_exists(__DIR__ . '/lib/CORE-Config.php'),
    'lib/LIB-Core.php' => file_exists(__DIR__ . '/lib/LIB-Core.php'),
    'index.php' => file_exists(__DIR__ . '/index.php')
];

foreach ($files as $file => $exists) {
    $debug['file_check'][$file] = $exists ? 'exists' : 'missing';
}

// Check PHP extensions
$extensions = ['pdo', 'pdo_mysql', 'pdo_pgsql', 'openssl', 'mbstring', 'zip'];
foreach ($extensions as $ext) {
    $debug['extension_check'][$ext] = extension_loaded($ext) ? 'loaded' : 'missing';
}

// Test database connection if environment is available
if (isset($_ENV['DB_HOST']) && isset($_ENV['DB_USER'])) {
    try {
        $host = $_ENV['DB_HOST'];
        $dbname = $_ENV['DB_NAME'] ?? 'postgres';
        $username = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'] ?? '';
        
        if ($_ENV['DB_CONNECTION'] === 'pgsql' || strpos($host, 'supabase') !== false) {
            $pdo = new PDO("pgsql:host=$host;dbname=$dbname", $username, $password);
        } else {
            $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        }
        
        $pdo->query('SELECT 1');
        $debug['database_check']['connection'] = 'success';
    } catch (Exception $e) {
        $debug['database_check']['connection'] = 'failed: ' . $e->getMessage();
    }
} else {
    $debug['database_check']['connection'] = 'no_credentials';
}

// Check storage directories
$storage_dirs = ['cache', 'logs', 'sessions', 'uploads'];
foreach ($storage_dirs as $dir) {
    $path = __DIR__ . '/storage/' . $dir;
    $debug['storage_check'][$dir] = [
        'exists' => is_dir($path),
        'writable' => is_writable($path)
    ];
}

// Try to load the app's environment
if (file_exists(__DIR__ . '/lib/CORE-Env.php')) {
    try {
        require_once __DIR__ . '/lib/CORE-Env.php';
        CoreEnv::load();
        $debug['core_env'] = 'loaded';
    } catch (Exception $e) {
        $debug['core_env'] = 'failed: ' . $e->getMessage();
    }
} else {
    $debug['core_env'] = 'file_missing';
}

echo json_encode($debug, JSON_PRETTY_PRINT);
?>
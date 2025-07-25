<?php
// Direct database connection test - bypasses application routing
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'test_type' => 'direct_database_connection',
    'environment_check' => [],
    'network_test' => [],
    'database_test' => []
];

// Environment variables check
$env_vars = [
    'DB_HOST' => $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'not_set',
    'DB_PORT' => $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 'not_set',
    'DB_NAME' => $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'not_set',
    'DB_USER' => $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'not_set',
    'DB_PASSWORD' => !empty($_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD')) ? 'set' : 'not_set',
    'POSTGRES_HOST' => $_ENV['POSTGRES_HOST'] ?? getenv('POSTGRES_HOST') ?: 'not_set',
    'POSTGRES_PORT' => $_ENV['POSTGRES_PORT'] ?? getenv('POSTGRES_PORT') ?: 'not_set',
    'POSTGRES_DB' => $_ENV['POSTGRES_DB'] ?? getenv('POSTGRES_DB') ?: 'not_set',
    'POSTGRES_USER' => $_ENV['POSTGRES_USER'] ?? getenv('POSTGRES_USER') ?: 'not_set',
    'POSTGRES_PASSWORD' => !empty($_ENV['POSTGRES_PASSWORD'] ?? getenv('POSTGRES_PASSWORD')) ? 'set' : 'not_set',
];

$result['environment_check'] = $env_vars;

// Get connection details (try both DB_* and POSTGRES_* formats)
$host = $env_vars['DB_HOST'] !== 'not_set' ? $env_vars['DB_HOST'] : $env_vars['POSTGRES_HOST'];
$port = $env_vars['DB_PORT'] !== 'not_set' ? $env_vars['DB_PORT'] : $env_vars['POSTGRES_PORT'];
$dbname = $env_vars['DB_NAME'] !== 'not_set' ? $env_vars['DB_NAME'] : $env_vars['POSTGRES_DB'];
$user = $env_vars['DB_USER'] !== 'not_set' ? $env_vars['DB_USER'] : $env_vars['POSTGRES_USER'];
$password = $env_vars['DB_PASSWORD'] !== 'not_set' ? ($_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD')) : ($_ENV['POSTGRES_PASSWORD'] ?? getenv('POSTGRES_PASSWORD'));

$result['connection_details'] = [
    'host' => $host,
    'port' => $port,
    'database' => $dbname,
    'user' => $user,
    'password_set' => !empty($password)
];

// Network connectivity test
if ($host !== 'not_set' && $port !== 'not_set') {
    // DNS Resolution
    $ip = gethostbyname($host);
    $result['network_test']['dns_resolution'] = $ip !== $host;
    $result['network_test']['resolved_ip'] = $ip;
    
    // Port connectivity
    $connection = @fsockopen($host, (int)$port, $errno, $errstr, 10);
    if ($connection) {
        $result['network_test']['port_connectivity'] = true;
        fclose($connection);
    } else {
        $result['network_test']['port_connectivity'] = false;
        $result['network_test']['connection_error'] = "$errno: $errstr";
    }
    
    // SSL test
    $context = stream_context_create([
        "ssl" => [
            "verify_peer" => false,
            "verify_peer_name" => false,
        ],
    ]);
    
    $ssl_connection = @stream_socket_client(
        "ssl://$host:$port", 
        $ssl_errno, 
        $ssl_errstr, 
        10, 
        STREAM_CLIENT_CONNECT, 
        $context
    );
    
    if ($ssl_connection) {
        $result['network_test']['ssl_support'] = true;
        fclose($ssl_connection);
    } else {
        $result['network_test']['ssl_support'] = false;
        $result['network_test']['ssl_error'] = "$ssl_errno: $ssl_errstr";
    }
}

// Database connection test
if ($host !== 'not_set' && $port !== 'not_set' && $dbname !== 'not_set' && $user !== 'not_set' && !empty($password)) {
    try {
        // Try with SSL mode require
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 10
        ]);
        
        $result['database_test']['connection'] = 'success';
        $result['database_test']['ssl_mode'] = 'require';
        
        // Test a simple query
        $stmt = $pdo->query('SELECT version()');
        $version = $stmt->fetchColumn();
        $result['database_test']['postgres_version'] = $version;
        
    } catch (PDOException $e) {
        $result['database_test']['connection'] = 'failed';
        $result['database_test']['error'] = $e->getMessage();
        $result['database_test']['ssl_mode'] = 'require';
        
        // Try without SSL requirement
        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 10
            ]);
            
            $result['database_test']['connection_no_ssl'] = 'success';
            $result['database_test']['ssl_mode'] = 'none';
            
        } catch (PDOException $e2) {
            $result['database_test']['connection_no_ssl'] = 'failed';
            $result['database_test']['error_no_ssl'] = $e2->getMessage();
        }
    }
} else {
    $result['database_test']['connection'] = 'skipped';
    $result['database_test']['reason'] = 'missing_environment_variables';
}

// PHP extensions check
$result['php_extensions'] = [
    'pdo' => extension_loaded('pdo'),
    'pdo_pgsql' => extension_loaded('pdo_pgsql'),
    'openssl' => extension_loaded('openssl'),
    'sockets' => extension_loaded('sockets')
];

echo json_encode($result, JSON_PRETTY_PRINT);
?>

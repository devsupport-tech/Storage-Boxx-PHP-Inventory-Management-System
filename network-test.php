<?php
// Network connectivity test for Supabase
header('Content-Type: application/json');

$supabase_host = 'db.gchxpxrrsinxcwxmkjzp.supabase.co';
$port = 6543; // Storage Boxx uses port 6543 for Supabase

$tests = [
    'dns_resolution' => false,
    'port_connectivity' => false,
    'ssl_support' => false
];

// Test 1: DNS Resolution
$ip = gethostbyname($supabase_host);
if ($ip !== $supabase_host) {
    $tests['dns_resolution'] = true;
    $tests['resolved_ip'] = $ip;
}

// Test 2: Port Connectivity
$connection = @fsockopen($supabase_host, $port, $errno, $errstr, 10);
if ($connection) {
    $tests['port_connectivity'] = true;
    fclose($connection);
} else {
    $tests['connection_error'] = "$errno: $errstr";
}

// Test 3: SSL Context
$context = stream_context_create([
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
    ],
]);

$ssl_connection = @stream_socket_client(
    "ssl://$supabase_host:$port", 
    $errno, 
    $errstr, 
    10, 
    STREAM_CLIENT_CONNECT, 
    $context
);

if ($ssl_connection) {
    $tests['ssl_support'] = true;
    fclose($ssl_connection);
} else {
    $tests['ssl_error'] = "$errno: $errstr";
}

// Environment variables check
$env_vars = [
    'POSTGRES_HOST' => $_ENV['POSTGRES_HOST'] ?? 'not_set',
    'POSTGRES_PORT' => $_ENV['POSTGRES_PORT'] ?? 'not_set',
    'POSTGRES_DB' => $_ENV['POSTGRES_DB'] ?? 'not_set (should be "db")',
    'POSTGRES_USER' => $_ENV['POSTGRES_USER'] ?? 'not_set',
    'POSTGRES_PASSWORD' => !empty($_ENV['POSTGRES_PASSWORD']) ? 'set' : 'not_set',
    'SUPABASE_URL' => $_ENV['SUPABASE_URL'] ?? 'not_set',
];

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'target_host' => $supabase_host,
    'target_port' => $port,
    'network_tests' => $tests,
    'environment_variables' => $env_vars,
    'php_extensions' => [
        'pdo' => extension_loaded('pdo'),
        'pdo_pgsql' => extension_loaded('pdo_pgsql'),
        'openssl' => extension_loaded('openssl')
    ]
];

echo json_encode($result, JSON_PRETTY_PRINT);
?>

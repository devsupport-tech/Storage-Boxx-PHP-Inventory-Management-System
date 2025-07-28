<?php
// Database setup script for Storage Boxx
// This will create the required database tables

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'setup_type' => 'database_initialization',
    'status' => 'starting',
    'steps' => []
];

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306;
    $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
    $user = $_ENV['DB_USER'] ?? getenv('DB_USER');
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
    
    $result['connection_details'] = [
        'host' => $host,
        'port' => $port,
        'database' => $dbname,
        'user' => $user
    ];
    
    // Connect to database (disable SSL for compatibility)
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => null
    ]);
    
    $result['steps'][] = 'Database connection: SUCCESS';
    
    // Read SQL schema file
    $sqlFile = __DIR__ . '/lib/SQL-Storage-Boxx-0.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("SQL schema file not found: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    $result['steps'][] = 'SQL schema file loaded: SUCCESS';
    
    // Parse SQL statements properly (handle multi-line statements)
    $statements = [];
    $currentStatement = '';
    $lines = explode("\n", $sql);
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Skip empty lines and comments
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        $currentStatement .= $line . " ";
        
        // Check if statement ends with semicolon
        if (substr(rtrim($line), -1) === ';') {
            $statements[] = trim($currentStatement);
            $currentStatement = '';
        }
    }
    
    $result['steps'][] = 'Found ' . count($statements) . ' SQL statements';
    
    // Execute each SQL statement
    $executed = 0;
    $errors = [];
    
    foreach ($statements as $index => $statement) {
        if (empty($statement)) continue;
        
        try {
            $result['steps'][] = 'Executing statement ' . ($index + 1) . ': ' . substr($statement, 0, 50) . '...';
            $pdo->exec($statement);
            $executed++;
        } catch (PDOException $e) {
            // Skip if table already exists
            if (strpos($e->getMessage(), 'already exists') !== false) {
                $result['steps'][] = 'Skipped existing table/index';
                continue;
            }
            $errors[] = 'Error executing statement ' . ($index + 1) . ': ' . $e->getMessage();
            $result['steps'][] = 'FAILED statement ' . ($index + 1) . ': ' . $e->getMessage();
        }
    }
    
    $result['steps'][] = "Executed $executed SQL statements successfully";
    
    if (!empty($errors)) {
        $result['errors'] = $errors;
        $result['status'] = 'completed_with_errors';
    } else {
        $result['status'] = 'success';
    }
    
    // Verify tables were created
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $result['created_tables'] = $tables;
    $result['steps'][] = 'Created ' . count($tables) . ' tables';
    
    // Check if settings table exists and has data
    if (in_array('settings', $tables)) {
        $settingsCount = $pdo->query("SELECT COUNT(*) FROM settings")->fetchColumn();
        $result['steps'][] = "Settings table has $settingsCount entries";
    }
    
} catch (Exception $e) {
    $result['status'] = 'error';
    $result['error'] = $e->getMessage();
    $result['steps'][] = 'FAILED: ' . $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>

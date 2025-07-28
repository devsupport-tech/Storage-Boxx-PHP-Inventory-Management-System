<?php
// Fix missing deliveries and purchases tables
// These failed during initial setup due to MySQL DATETIME default value issues

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'create_missing_tables',
    'status' => 'starting'
];

try {
    // Database connection
    $host = $_ENV['DB_HOST'] ?? getenv('DB_HOST');
    $port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: 3306;
    $dbname = $_ENV['DB_NAME'] ?? getenv('DB_NAME');
    $user = $_ENV['DB_USER'] ?? getenv('DB_USER');
    $password = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD');
    
    // Connect to database (disable SSL for compatibility)
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false,
        PDO::MYSQL_ATTR_SSL_CA => null
    ]);
    
    $result['database_connection'] = 'SUCCESS';
    
    // Check which tables are missing
    $existingTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $result['existing_tables'] = $existingTables;
    
    $missingTables = [];
    if (!in_array('purchases', $existingTables)) {
        $missingTables[] = 'purchases';
    }
    if (!in_array('deliveries', $existingTables)) {
        $missingTables[] = 'deliveries';
    }
    
    $result['missing_tables'] = $missingTables;
    
    if (empty($missingTables)) {
        $result['status'] = 'no_action_needed';
        $result['message'] = 'All tables already exist';
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }
    
    $created = [];
    $errors = [];
    
    // Create purchases table (fixed DATETIME default)
    if (in_array('purchases', $missingTables)) {
        try {
            $sql = "CREATE TABLE `purchases` (
              `p_id` bigint(20) NOT NULL AUTO_INCREMENT,
              `p_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `p_status` varchar(1) NOT NULL DEFAULT 'P',
              `p_notes` text DEFAULT NULL,
              `sup_id` bigint(20) NOT NULL,
              PRIMARY KEY (`p_id`),
              KEY `p_date` (`p_date`),
              KEY `p_status` (`p_status`),
              KEY `sup_id` (`sup_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $pdo->exec($sql);
            $created[] = 'purchases';
            $result['purchases_table'] = 'CREATED';
        } catch (Exception $e) {
            $errors[] = 'purchases: ' . $e->getMessage();
        }
    }
    
    // Create deliveries table (fixed DATETIME default)
    if (in_array('deliveries', $missingTables)) {
        try {
            $sql = "CREATE TABLE `deliveries` (
              `d_id` bigint(20) NOT NULL AUTO_INCREMENT,
              `d_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `d_status` varchar(1) NOT NULL DEFAULT 'P',
              `d_notes` text DEFAULT NULL,
              `cus_id` bigint(20) NOT NULL,
              PRIMARY KEY (`d_id`),
              KEY `d_date` (`d_date`),
              KEY `d_status` (`d_status`),
              KEY `cus_id` (`cus_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
            
            $pdo->exec($sql);
            $created[] = 'deliveries';
            $result['deliveries_table'] = 'CREATED';
        } catch (Exception $e) {
            $errors[] = 'deliveries: ' . $e->getMessage();
        }
    }
    
    $result['created_tables'] = $created;
    $result['errors'] = $errors;
    
    if (!empty($created)) {
        $result['status'] = 'success';
        $result['message'] = 'Missing tables created successfully';
    } else {
        $result['status'] = 'failed';
        $result['message'] = 'Failed to create tables';
    }
    
    // Verify final table count
    $finalTables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $result['final_table_count'] = count($finalTables);
    $result['final_tables'] = $finalTables;
    
} catch (Exception $e) {
    $result['status'] = 'error';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>

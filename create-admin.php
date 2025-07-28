<?php
// Create first admin user for Storage Boxx
// Run this once to create your admin account

header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 1);

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'action' => 'create_admin_user',
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
    
    // Check if users table exists
    $tables = $pdo->query("SHOW TABLES LIKE 'users'")->fetchAll();
    if (empty($tables)) {
        throw new Exception("Users table does not exist. Run setup-database.php first.");
    }
    
    // Check if admin user already exists
    $existingAdmin = $pdo->query("SELECT COUNT(*) FROM users WHERE user_level = 'A'")->fetchColumn();
    if ($existingAdmin > 0) {
        $result['status'] = 'admin_exists';
        $result['message'] = 'Admin user already exists. Check existing users below.';
        
        // Show existing users
        $users = $pdo->query("SELECT user_id, user_name, user_email, user_level FROM users")->fetchAll();
        $result['existing_users'] = $users;
        
        echo json_encode($result, JSON_PRETTY_PRINT);
        exit;
    }
    
    // Create admin user with your specified credentials
    $adminName = "Supply Circuit Admin";
    $adminEmail = "thesupplycircuit@gmail.com";
    $adminPassword = "SupplyBox2024!Secure#789"; // Unique secure password
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    // Insert admin user
    $stmt = $pdo->prepare("INSERT INTO users (user_name, user_email, user_level, user_password) VALUES (?, ?, ?, ?)");
    $stmt->execute([$adminName, $adminEmail, "A", $hashedPassword]);
    
    $adminId = $pdo->lastInsertId();
    
    $result['status'] = 'success';
    $result['admin_created'] = [
        'user_id' => $adminId,
        'name' => $adminName,
        'email' => $adminEmail,
        'password' => $adminPassword,
        'level' => 'A (Admin)'
    ];
    $result['login_instructions'] = [
        'url' => 'https://inventory.thesupplycircuit.co/login',
        'email' => $adminEmail,
        'password' => $adminPassword,
        'note' => 'IMPORTANT: You can change this password after first login in the user settings!'
    ];
    
    // Verify user was created
    $verifyUser = $pdo->query("SELECT user_id, user_name, user_email, user_level FROM users WHERE user_id = $adminId")->fetch();
    $result['verification'] = $verifyUser;
    
} catch (Exception $e) {
    $result['status'] = 'error';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>

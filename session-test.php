<?php
// Session test script for Storage Boxx
header('Content-Type: application/json');

$result = [
    'timestamp' => date('Y-m-d H:i:s'),
    'test_type' => 'session_diagnostics'
];

try {
    // Test session configuration
    $result['session_config'] = [
        'session_save_path' => session_save_path(),
        'session_name' => session_name(),
        'session_id' => session_id(),
        'session_status' => session_status(),
        'session_module_name' => session_module_name()
    ];
    
    // Test session directory permissions
    $sessionPath = session_save_path() ?: '/tmp';
    $result['session_directory'] = [
        'path' => $sessionPath,
        'exists' => is_dir($sessionPath),
        'writable' => is_writable($sessionPath),
        'readable' => is_readable($sessionPath)
    ];
    
    // Test starting a session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
        $result['session_start'] = 'SUCCESS';
        $result['new_session_id'] = session_id();
        
        // Test writing to session
        $_SESSION['test'] = 'session_working';
        $result['session_write'] = 'SUCCESS';
        
        // Test reading from session
        $result['session_read'] = $_SESSION['test'] ?? 'FAILED';
        
        session_destroy();
        $result['session_cleanup'] = 'SUCCESS';
    } else {
        $result['session_start'] = 'ALREADY_ACTIVE';
    }
    
    $result['status'] = 'success';
    
} catch (Exception $e) {
    $result['status'] = 'error';
    $result['error'] = $e->getMessage();
}

echo json_encode($result, JSON_PRETTY_PRINT);
?>

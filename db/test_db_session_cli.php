<?php
require_once __DIR__ . '/../config.php';

echo "Testing Session Handler...\n";

try {
    // Manually test the handler methods since session_start can't work easily in CLI without headers adjustments
    $pdo = getPDO();
    $handler = new DbSessionHandler($pdo);
    
    $test_id = 'test_session_' . time();
    $test_data = 'user_id|s:1:"1";username|s:5:"admin";';
    
    // 1. Write
    if ($handler->write($test_id, $test_data)) {
        echo "[PASS] Write session successful.\n";
    } else {
        echo "[FAIL] Write session failed.\n";
    }
    
    // 2. Read
    $read_data = $handler->read($test_id);
    if ($read_data === $test_data) {
        echo "[PASS] Read session successful (Data matched).\n";
    } else {
        echo "[FAIL] Read session mismatch.\n";
        echo "Expected: $test_data\n";
        echo "Got: $read_data\n";
    }
    
    // 3. Destroy
    if ($handler->destroy($test_id)) {
        echo "[PASS] Destroy session successful.\n";
    } else {
        echo "[FAIL] Destroy session failed.\n";
    }
    
    // 4. Verify Destroy
    $read_after_destroy = $handler->read($test_id);
    if ($read_after_destroy === '') {
        echo "[PASS] Destroy verification successful (Data is empty).\n";
    } else {
        echo "[FAIL] Data still exists after destroy.\n";
    }

} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}

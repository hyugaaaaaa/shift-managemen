<?php
require_once __DIR__ . '/config.php';

echo "Testing DB Connection...\n";
echo "Host: " . DB_HOST . "\n";
echo "User: " . DB_USER . "\n";

try {
    $pdo = getPDO();
    echo "Connection Successful!\n";
} catch (PDOException $e) {
    echo "Connection Failed: " . $e->getMessage() . "\n";
    
    // Try forcing localhost
    echo "Retrying with localhost...\n";
    try {
        $dsn = 'mysql:host=localhost;dbname='.DB_NAME.';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        echo "Connection with localhost Successful!\n";
    } catch (PDOException $e2) {
        echo "Connection with localhost Failed: " . $e2->getMessage() . "\n";
    }
}

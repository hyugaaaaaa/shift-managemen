<?php
require_once __DIR__ . '/../config.php';

try {
    // Connect without DB name to Create DB
    $dsn = 'mysql:host='.DB_HOST.';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    $newDbName = 'shift_management_new';
    
    // Create DB
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$newDbName` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    echo "Database $newDbName created (or already exists).\n";
    
} catch (Exception $e) {
    echo "Error creating database: " . $e->getMessage() . "\n";
    exit(1);
}

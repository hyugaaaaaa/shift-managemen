<?php
require_once __DIR__ . '/../config.php';

echo "Migration Start: Fixing Username Unique Constraint (Retry)...\n";

try {
    // Force specific DSN that might work if default fails
    // Try 127.0.0.1 explicitly with port 3306
    $dsn = 'mysql:host=127.0.0.1;port=3306;dbname='.DB_NAME.';charset=utf8mb4';
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    
    // 1. Check if 'username' unique index exists
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'username'");
    if ($stmt->fetch()) {
        echo "Dropping unique index 'username'...\n";
        $pdo->exec("ALTER TABLE `users` DROP INDEX `username`");
    } else {
        echo "Index 'username' not found (skipped drop).\n";
    }

    // 2. Add composite unique index (company_id, username)
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'unique_company_username'");
    if (!$stmt->fetch()) {
        echo "Adding composite unique index 'unique_company_username'...\n";
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE KEY `unique_company_username` (`company_id`, `username`)");
    }

    echo "Migration Completed Successfully.\n";

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    
    // Fallback to localhost
    try {
        echo "Retrying with localhost...\n";
        $dsn = 'mysql:host=localhost;dbname='.DB_NAME.';charset=utf8mb4';
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        
        // Same logic
        $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'username'");
        if ($stmt->fetch()) {
            $pdo->exec("ALTER TABLE `users` DROP INDEX `username`");
        }
        $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'unique_company_username'");
        if (!$stmt->fetch()) {
            $pdo->exec("ALTER TABLE `users` ADD UNIQUE KEY `unique_company_username` (`company_id`, `username`)");
        }
        echo "Migration Completed Successfully (localhost).\n";
        
    } catch(PDOException $e2) {
         echo "Migration Fully Failed: " . $e2->getMessage() . "\n";
         exit(1);
    }
}

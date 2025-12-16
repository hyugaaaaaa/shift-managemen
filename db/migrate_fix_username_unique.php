<?php
require_once __DIR__ . '/../config.php';

echo "Migration Start: Fixing Username Unique Constraint...\n";

try {
    $pdo = getPDO();
    
    // 1. Check if 'username' unique index exists
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'username'");
    if ($stmt->fetch()) {
        echo "Dropping unique index 'username'...\n";
        $pdo->exec("ALTER TABLE `users` DROP INDEX `username`");
    } else {
        echo "Index 'username' not found (skipped drop).\n";
    }

    // 2. Add composite unique index (company_id, username)
    // First check if it already exists to avoid error
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'unique_company_username'");
    if (!$stmt->fetch()) {
        echo "Adding composite unique index 'unique_company_username'...\n";
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE KEY `unique_company_username` (`company_id`, `username`)");
    } else {
        echo "Index 'unique_company_username' already exists.\n";
    }

    echo "Migration Completed Successfully.\n";

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}

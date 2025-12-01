<?php
require_once __DIR__ . '/../config.php';

echo "Starting migration for payslips...\n";

$pdo = getPDO();

// Add payslip_consent to users table
try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'payslip_consent'");
    if ($stmt->fetch()) {
        echo "Column 'payslip_consent' already exists in 'users'.\n";
    } else {
        echo "Adding 'payslip_consent' and 'payslip_consent_date' to 'users'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN payslip_consent TINYINT(1) NOT NULL DEFAULT 0");
        $pdo->exec("ALTER TABLE users ADD COLUMN payslip_consent_date DATETIME DEFAULT NULL");
        echo "Done.\n";
    }
} catch (PDOException $e) {
    echo "Error adding columns: " . $e->getMessage() . "\n";
}

echo "Migration completed.\n";

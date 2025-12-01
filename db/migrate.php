<?php
require_once __DIR__ . '/../config.php';

echo "Starting migration...\n";

$pdo = getPDO();

// 1. Add transportation_expense to users table
try {
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'transportation_expense'");
    if ($stmt->fetch()) {
        echo "Column 'transportation_expense' already exists in 'users'.\n";
    } else {
        echo "Adding 'transportation_expense' to 'users'...\n";
        $pdo->exec("ALTER TABLE users ADD COLUMN transportation_expense DECIMAL(8,2) NOT NULL DEFAULT 0.00 AFTER hourly_rate");
        echo "Done.\n";
    }
} catch (PDOException $e) {
    echo "Error adding column: " . $e->getMessage() . "\n";
}

// 2. Create system_settings table
try {
    echo "Creating 'system_settings' table if not exists...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `system_settings` (
          `setting_key` VARCHAR(50) NOT NULL,
          `setting_value` VARCHAR(255) NOT NULL,
          PRIMARY KEY (`setting_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // Insert default values
    echo "Inserting default settings...\n";
    $pdo->exec("INSERT IGNORE INTO `system_settings` (`setting_key`, `setting_value`) VALUES ('closing_day', '15'), ('payment_day', '25')");
    echo "Done.\n";
    
} catch (PDOException $e) {
    echo "Error creating table: " . $e->getMessage() . "\n";
}

echo "Migration completed.\n";

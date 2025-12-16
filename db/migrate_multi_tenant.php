<?php
require_once __DIR__ . '/../config.php';

echo "Migration Start...\n";

try {
    $pdo = getPDO();
    
    // 1. Create companies table
    echo "Creating 'companies' table...\n";
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS `companies` (
          `company_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
          `company_name` VARCHAR(100) NOT NULL,
          `company_code` VARCHAR(20) NOT NULL UNIQUE,
          `representative_name` VARCHAR(100),
          `address` VARCHAR(255),
          `phone_number` VARCHAR(20),
          `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`company_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    echo "Done.\n";

    // 2. Create default company for existing data
    echo "Checking/Creating default company...\n";
    $stmt = $pdo->query("SELECT company_id FROM companies WHERE company_code = 'DEFAULT001' LIMIT 1");
    $defaultCompanyId = $stmt->fetchColumn();

    if (!$defaultCompanyId) {
        $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code) VALUES (?, ?)");
        $stmt->execute(['Default Company', 'DEFAULT001']); // 既存データ用のデフォルト企業
        $defaultCompanyId = $pdo->lastInsertId();
        echo "Created default company (ID: $defaultCompanyId)\n";
    } else {
        echo "Default company exists (ID: $defaultCompanyId)\n";
    }

    // 3. Alter 'users' table
    echo "Altering 'users' table...\n";
    
    // Check if column exists
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'company_id'");
    if (!$stmt->fetch()) {
        // Add company_id
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `company_id` INT UNSIGNED NOT NULL DEFAULT {$defaultCompanyId} AFTER `user_id`");
        echo "Added 'company_id' column to users.\n";
        
        // Add FK
        $pdo->exec("ALTER TABLE `users` ADD CONSTRAINT `fk_users_company` FOREIGN KEY (`company_id`) REFERENCES `companies`(`company_id`) ON DELETE CASCADE");
        echo "Added Foreign Key to users.\n";
    }

    // Terms agreement columns
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'is_agreed_terms'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `is_agreed_terms` TINYINT(1) NOT NULL DEFAULT 0");
        $pdo->exec("ALTER TABLE `users` ADD COLUMN `agreed_terms_at` DATETIME DEFAULT NULL");
        echo "Added terms agreement columns to users.\n";
    }
    
    // Modify Unique Constraint for Email
    // Check existing index
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'email'");
    if ($stmt->fetch()) {
        $pdo->exec("ALTER TABLE `users` DROP INDEX `email`");
        echo "Dropped existing unique index 'email'.\n";
    }
    
    $stmt = $pdo->query("SHOW INDEX FROM users WHERE Key_name = 'unique_email_company'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `users` ADD UNIQUE KEY `unique_email_company` (`email`, `company_id`)");
        echo "Added composite unique index 'unique_email_company'.\n";
    }

    // 4. Alter 'system_settings' table
    echo "Altering 'system_settings' table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM system_settings LIKE 'company_id'");
    if (!$stmt->fetch()) {
        $pdo->exec("ALTER TABLE `system_settings` ADD COLUMN `company_id` INT UNSIGNED NOT NULL DEFAULT {$defaultCompanyId} FIRST");
        
        // PK change: Since we can't easily drop PK if we don't know the exact name or if it's referenced, 
        // usually it's PRIMARY KEY.
        $pdo->exec("ALTER TABLE `system_settings` DROP PRIMARY KEY");
        $pdo->exec("ALTER TABLE `system_settings` ADD PRIMARY KEY (`company_id`, `setting_key`)");
        echo "Modified PK for system_settings.\n";
    }

    // 5. Alter 'password_resets' table
    echo "Altering 'password_resets' table...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM password_resets LIKE 'company_id'");
    if (!$stmt->fetch()) {
        // Add company_id. 
        // Existing records might be tricky, but since password reset tokens are ephemeral, 
        // we can either delete them or set to default company.
        $pdo->exec("DELETE FROM password_resets"); // Clear old tokens to avoid inconsistency
        $pdo->exec("ALTER TABLE `password_resets` ADD COLUMN `company_id` INT UNSIGNED NOT NULL AFTER `email`");
        echo "Added 'company_id' column to password_resets (Old tokens cleared).\n";
    }

    echo "Migration Completed Successfully.\n";

} catch (PDOException $e) {
    echo "Migration Failed: " . $e->getMessage() . "\n";
    exit(1);
}

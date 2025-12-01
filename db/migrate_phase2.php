<?php
require_once __DIR__ . '/../config.php';

echo "Starting Phase 2 Migration...\n";

try {
    $pdo = getPDO();

    // 1. Users table extension (email)
    echo "Checking users table for email column...\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'email'");
    if ($stmt->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD COLUMN email VARCHAR(255) UNIQUE AFTER username");
        echo "Added email column to users table.\n";
    } else {
        echo "email column already exists.\n";
    }

    // 2. Skills Management
    echo "Creating skills tables...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS skills (
        skill_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        skill_name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (skill_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_skills (
        user_id INT UNSIGNED NOT NULL,
        skill_id INT UNSIGNED NOT NULL,
        PRIMARY KEY (user_id, skill_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (skill_id) REFERENCES skills(skill_id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 3. Shift Exchanges
    echo "Creating shift_exchanges table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS shift_exchanges (
        exchange_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        requester_user_id INT UNSIGNED NOT NULL,
        target_shift_id INT UNSIGNED NOT NULL,
        requested_user_id INT UNSIGNED DEFAULT NULL,
        status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
        reason TEXT,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (exchange_id),
        FOREIGN KEY (requester_user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (target_shift_id) REFERENCES shifts_scheduled(schedule_id) ON DELETE CASCADE,
        FOREIGN KEY (requested_user_id) REFERENCES users(user_id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 4. Operation Logs
    echo "Creating operation_logs table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS operation_logs (
        log_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED DEFAULT NULL,
        action VARCHAR(100) NOT NULL,
        target_id INT UNSIGNED DEFAULT NULL,
        details TEXT,
        ip_address VARCHAR(45),
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (log_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // 5. Password Resets
    echo "Creating password_resets table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS password_resets (
        email VARCHAR(255) NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at DATETIME NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX (email),
        INDEX (token)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Phase 2 Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

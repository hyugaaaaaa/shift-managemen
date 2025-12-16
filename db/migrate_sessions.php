<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    
    echo "Creating sessions table...\n";
    
    $sql = "
    CREATE TABLE IF NOT EXISTS `sessions` (
        `id` VARCHAR(128) NOT NULL,
        `data` BLOB,
        `timestamp` INT UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        INDEX (`timestamp`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    
    $pdo->exec($sql);
    echo "Table 'sessions' created successfully.\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

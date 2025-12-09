<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
    echo "Columns in users table: " . implode(", ", $columns) . "\n";
    
    if (!in_array('email', $columns)) {
        echo "Email column is MISSING.\n";
    } else {
        echo "Email column EXISTS.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

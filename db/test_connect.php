<?php
require_once __DIR__ . '/../config.php';

try {
    echo "Attempting to connect to DB...\n";
    $pdo = getPDO();
    echo "Connection successful!\n";
    $stmt = $pdo->query("SELECT VERSION()");
    echo "DB Version: " . $stmt->fetchColumn() . "\n";
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}

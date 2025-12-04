<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    $sql = file_get_contents(__DIR__ . '/migrate_shift_templates.sql');
    $pdo->exec($sql);
    echo "Migration successful: shift_templates table created.\n";
} catch (Exception $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
}

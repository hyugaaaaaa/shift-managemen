<?php
require_once __DIR__ . '/../config.php';

try {
    $pdo = getPDO();
    $pdo->query("SET FOREIGN_KEY_CHECKS = 0");

    $tables = [
        'attendance_records',
        'shift_exchanges',
        'user_skills',
        'skills',
        'shifts_requested',
        'shifts_scheduled',
        'system_settings',
        'users',
        'companies',
        'operation_logs',
        'password_resets',
        'announcements',
        'shift_templates',
        'holidays',
        'mail_queue',
        'sessions'
    ];

    foreach ($tables as $table) {
        $pdo->exec("DROP TABLE IF EXISTS `$table`");
        echo "Dropped table $table\n";
    }

    $pdo->query("SET FOREIGN_KEY_CHECKS = 1");
    echo "All tables dropped successfully.\n";

} catch (Exception $e) {
    echo "Error dropping tables: " . $e->getMessage() . "\n";
    exit(1);
}

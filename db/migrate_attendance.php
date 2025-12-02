<?php
require_once __DIR__ . '/../config.php';

echo "Starting Attendance Migration...\n";

try {
    $pdo = getPDO();

    // attendance_records テーブルの作成
    echo "Creating attendance_records table...\n";
    $pdo->exec("CREATE TABLE IF NOT EXISTS attendance_records (
        attendance_id INT UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id INT UNSIGNED NOT NULL,
        schedule_id INT UNSIGNED DEFAULT NULL,
        date DATE NOT NULL,
        clock_in_time DATETIME,
        clock_out_time DATETIME,
        status ENUM('present', 'absent', 'late', 'early_leave', 'paid_leave') NOT NULL DEFAULT 'present',
        notes TEXT,
        is_approved BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (attendance_id),
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (schedule_id) REFERENCES shifts_scheduled(schedule_id) ON DELETE SET NULL,
        UNIQUE KEY unique_user_date (user_id, date) -- 同じユーザーの同日レコードは1つのみ
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    echo "Attendance Migration completed successfully.\n";

} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}

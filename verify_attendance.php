<?php
require_once __DIR__ . '/config.php';

echo "Starting Verification...\n";

try {
    $pdo = getPDO();
    $pdo->beginTransaction();

    // 1. テストユーザー作成
    $username = 'test_user_' . time();
    $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, user_type, hourly_rate) VALUES (?, 'hash', 'part-time', 1000)");
    $stmt->execute([$username]);
    $user_id = $pdo->lastInsertId();
    echo "Created test user: ID $user_id\n";

    // 2. シフト予定作成 (10:00 - 15:00, 5時間)
    $date = date('Y-m-d');
    $stmt = $pdo->prepare("INSERT INTO shifts_scheduled (user_id, shift_date, start_time, end_time) VALUES (?, ?, '10:00:00', '15:00:00')");
    $stmt->execute([$user_id, $date]);
    echo "Created schedule: 10:00 - 15:00\n";

    // 3. 勤怠実績作成 (残業: 10:00 - 16:00, 6時間)
    $stmt = $pdo->prepare("INSERT INTO attendance_records (user_id, date, clock_in_time, clock_out_time, status, is_approved) VALUES (?, ?, ?, ?, 'present', 1)");
    $stmt->execute([$user_id, $date, "$date 10:00:00", "$date 16:00:00"]);
    echo "Created attendance: 10:00 - 16:00 (Overtime)\n";

    // 4. マージデータ取得確認
    $records = get_merged_work_records($pdo, $date, $date, $user_id);
    $rec = $records[$user_id][$date];
    
    if ($rec['type'] === 'attendance' && $rec['end'] === '16:00:00') {
        echo "PASS: get_merged_work_records returned attendance data correctly.\n";
    } else {
        echo "FAIL: get_merged_work_records returned unexpected data.\n";
        print_r($rec);
    }

    // 5. 給与計算ロジック確認 (簡易)
    $times = calculate_shift_minutes($date, $rec['start'], $rec['end']);
    if ($times['total_minutes'] == 360) { // 6 hours * 60
        echo "PASS: Calculated minutes is 360 (6 hours).\n";
    } else {
        echo "FAIL: Calculated minutes is {$times['total_minutes']}.\n";
    }

    // クリーンアップ (ロールバック)
    $pdo->rollBack();
    echo "Test finished. Rolled back changes.\n";

} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

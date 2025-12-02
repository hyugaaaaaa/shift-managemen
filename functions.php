<?php

/**
 * システム設定値を取得する
 * @param PDO $pdo
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_system_setting($pdo, $key, $default = null) {
    $stmt = $pdo->prepare("SELECT setting_value FROM system_settings WHERE setting_key = ?");
    $stmt->execute([$key]);
    $val = $stmt->fetchColumn();
    return $val !== false ? $val : $default;
}

/**
 * シフトの勤務時間を計算する
 * @param string $date シフト日付 (Y-m-d)
 * @param string $start_time 開始時刻 (H:i:s)
 * @param string $end_time 終了時刻 (H:i:s)
 * @return array [total_minutes, night_minutes]
 */
function calculate_shift_minutes($date, $start_time, $end_time) {
    $start = strtotime($date . ' ' . $start_time);
    $end = strtotime($date . ' ' . $end_time);
    
    // 日またぎ対応
    if ($end <= $start) {
        $end += 86400;
    }
    
    $total_minutes = ($end - $start) / 60;
    $night_minutes = 0;
    
    // 深夜時間計算（22:00 - 05:00）
    // 簡易実装: 1分ごとにループして判定
    // パフォーマンスが気になる場合は数式計算に変更可
    for ($t = $start; $t < $end; $t += 60) {
        $H = (int)date('H', $t);
        if ($H >= 22 || $H < 5) {
            $night_minutes++;
        }
    }
    
    return [
        'total_minutes' => $total_minutes,
        'night_minutes' => $night_minutes,
        'normal_minutes' => $total_minutes - $night_minutes
    ];
}

/**
 * 給与を計算する
 * @param int $normal_minutes 通常勤務時間（分）
 * @param int $night_minutes 深夜勤務時間（分）
 * @param float $hourly_rate 時給
 * @return array [pay_normal, pay_night, total_pay] (交通費は別途計算)
 */
function calculate_salary_amount($normal_minutes, $night_minutes, $hourly_rate) {
    $pay_normal = floor($normal_minutes / 60 * $hourly_rate);
    $pay_night = floor($night_minutes / 60 * $hourly_rate * 1.25); // 深夜1.25倍
    
    return [
        'pay_normal' => $pay_normal,
        'pay_night' => $pay_night,
        'subtotal' => $pay_normal + $pay_night
    ];
}

/**
 * CSRFトークンを生成する
 */
function generate_csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * CSRFトークンを検証する
 * @param string $token
 * @return bool
 */
function validate_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
/**
 * 指定期間の勤務実績データ（予定と実績をマージしたもの）を取得する
 * @param PDO $pdo
 * @param string $start_date
 * @param string $end_date
 * @param int|null $target_user_id 特定ユーザーのみ取得する場合に指定
 * @return array [user_id => [date => record]]
 */
function get_merged_work_records($pdo, $start_date, $end_date, $target_user_id = null) {
    // 1. シフト予定の取得
    $sql_sched = "SELECT * FROM shifts_scheduled WHERE shift_date BETWEEN ? AND ?";
    $params_sched = [$start_date, $end_date];
    if ($target_user_id) {
        $sql_sched .= " AND user_id = ?";
        $params_sched[] = $target_user_id;
    }
    $stmt = $pdo->prepare($sql_sched);
    $stmt->execute($params_sched);
    
    $merged_data = [];
    foreach ($stmt->fetchAll() as $row) {
        $uid = $row['user_id'];
        $date = $row['shift_date'];
        if (!isset($merged_data[$uid])) $merged_data[$uid] = [];
        $merged_data[$uid][$date] = [
            'type' => 'schedule',
            'start' => $row['start_time'],
            'end' => $row['end_time'],
            'schedule_id' => $row['schedule_id'],
            'status' => 'scheduled' // 仮
        ];
    }

    // 2. 勤怠実績の取得
    $sql_att = "SELECT * FROM attendance_records WHERE date BETWEEN ? AND ?";
    $params_att = [$start_date, $end_date];
    if ($target_user_id) {
        $sql_att .= " AND user_id = ?";
        $params_att[] = $target_user_id;
    }
    $stmt = $pdo->prepare($sql_att);
    $stmt->execute($params_att);
    
    foreach ($stmt->fetchAll() as $row) {
        $uid = $row['user_id'];
        $date = $row['date'];
        
        // 実績があれば上書き、または新規追加
        // status が absent (欠勤) の場合は、時間は0にするがレコードは残す
        
        $start = $row['clock_in_time'] ? date('H:i:s', strtotime($row['clock_in_time'])) : null;
        $end = $row['clock_out_time'] ? date('H:i:s', strtotime($row['clock_out_time'])) : null;
        
        if ($row['status'] === 'absent') {
            $start = null;
            $end = null;
        }

        if (!isset($merged_data[$uid])) $merged_data[$uid] = [];
        
        $merged_data[$uid][$date] = [
            'type' => 'attendance',
            'start' => $start,
            'end' => $end,
            'attendance_id' => $row['attendance_id'],
            'status' => $row['status'],
            'is_approved' => $row['is_approved'],
            'notes' => $row['notes']
        ];
    }
    
    return $merged_data;
}

/**
 * 日付から日本語の曜日を取得する
 * @param string $date Y-m-d
 * @return string (月), (火) etc.
 */
function get_day_of_week_ja($date) {
    $week = ['日', '月', '火', '水', '木', '金', '土'];
    $w = (int)date('w', strtotime($date));
    return '(' . $week[$w] . ')';
}

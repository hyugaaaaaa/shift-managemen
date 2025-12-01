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

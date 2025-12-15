<?php

use App\Services\ShiftService;
use App\Services\SalaryService;
use App\Services\MailService;
use App\Security\Csrf;
use App\Models\SystemSetting;
use App\Utils\DateHelper;

/**
 * システム設定値を取得する
 * @param PDO $pdo
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function get_system_setting($pdo, $key, $default = null) {
    return SystemSetting::get($pdo, $key, $default);
}

/**
 * シフトの勤務時間を計算する
 * @param string $date シフト日付 (Y-m-d)
 * @param string $start_time 開始時刻 (H:i:s)
 * @param string $end_time 終了時刻 (H:i:s)
 * @return array [total_minutes, night_minutes]
 */
function calculate_shift_minutes($date, $start_time, $end_time) {
    $service = new ShiftService();
    return $service->calculateMinutes($date, $start_time, $end_time);
}

/**
 * 給与を計算する
 * @param int $normal_minutes 通常勤務時間（分）
 * @param int $night_minutes 深夜勤務時間（分）
 * @param float $hourly_rate 時給
 * @return array [pay_normal, pay_night, total_pay] (交通費は別途計算)
 */
function calculate_salary_amount($normal_minutes, $night_minutes, $hourly_rate) {
    $service = new SalaryService();
    return $service->calculate($normal_minutes, $night_minutes, $hourly_rate);
}

/**
 * CSRFトークンを生成する
 */
function generate_csrf_token() {
    return Csrf::generate();
}

/**
 * CSRFトークンを検証する
 * @param string $token
 * @return bool
 */
function validate_csrf_token($token) {
    return Csrf::validate($token);
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
    $service = new ShiftService();
    return $service->getMergedWorkRecords($pdo, $start_date, $end_date, $target_user_id);
}

/**
 * 日付から日本語の曜日を取得する
 * @param string $date Y-m-d
 * @return string (月), (火) etc.
 */
function get_day_of_week_ja($date) {
    return DateHelper::getDayOfWeekJa($date);
}

/**
 * HTMLエスケープを行うヘルパー関数
 * @param string $str
 * @return string
 */
function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * メールを送信する
 * @param string $to 送信先アドレス
 * @param string $subject 件名
 * @param string $body 本文
 * @return bool 送信成功ならtrue
 */
function send_mail($to, $subject, $body) {
    $service = new MailService();
    // 同期送信から非同期送信キューへの登録に変更
    return $service->queue($to, $subject, $body);
}

/**
 * ログをファイルに書き込む
 * @param string $message
 * @param string $filename
 */
function write_log($message, $filename = 'app.log') {
    $filepath = __DIR__ . '/' . $filename;
    $log_message = "[" . date('Y-m-d H:i:s') . "] " . $message . "\n";
    file_put_contents($filepath, $log_message, FILE_APPEND);
}

/**
 * バックグラウンドでPHPスクリプトを実行する (Windows/XAMPP想定)
 * @param string $script_path 実行するPHPスクリプトの絶対パス
 */
function launch_background_process($script_path) {
    if (!file_exists($script_path)) {
        write_log("Background process launch failed: Script not found at $script_path", 'error.log');
        return;
    }

    $phpPath = defined('PHP_BINARY_PATH') ? PHP_BINARY_PATH : 'php';
    
    // Windows specifically
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $cmd = 'start /B "" "' . $phpPath . '" "' . $script_path . '" > NUL 2>&1';
        pclose(popen($cmd, 'r'));
    } else {
        // Unix-like fallback (just in case)
        $cmd = $phpPath . ' ' . $script_path . ' > /dev/null 2>&1 &';
        exec($cmd);
    }
}






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
    $company_id = get_current_company_id();
    return SystemSetting::get($pdo, $key, $default, $company_id);
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
function get_merged_work_records($pdo, $start_date, $end_date, $target_user_id = null, $user_ids = null) {
    // shifts_scheduled（確定シフト）を取得
    $sql_scheduled = 'SELECT user_id, shift_date, start_time, end_time FROM shifts_scheduled WHERE shift_date BETWEEN ? AND ?';
    $params_scheduled = [$start_date, $end_date];
    
    if ($target_user_id !== null) {
        $sql_scheduled .= ' AND user_id = ?';
        $params_scheduled[] = $target_user_id;
    } elseif ($user_ids !== null && is_array($user_ids) && count($user_ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        $sql_scheduled .= " AND user_id IN ($placeholders)";
        $params_scheduled = array_merge($params_scheduled, $user_ids);
    }
    
    $stmt_scheduled = $pdo->prepare($sql_scheduled);
    $stmt_scheduled->execute($params_scheduled);
    $scheduled_rows = $stmt_scheduled->fetchAll();

    // attendance_records（実績）を取得
    $sql_attendance = 'SELECT user_id, date AS shift_date, clock_in_time AS start_time, clock_out_time AS end_time FROM attendance_records WHERE date BETWEEN ? AND ?';
    $params_attendance = [$start_date, $end_date];
    
    if ($target_user_id !== null) {
        $sql_attendance .= ' AND user_id = ?';
        $params_attendance[] = $target_user_id;
    } elseif ($user_ids !== null && is_array($user_ids) && count($user_ids) > 0) {
        $placeholders = implode(',', array_fill(0, count($user_ids), '?'));
        $sql_attendance .= " AND user_id IN ($placeholders)";
        $params_attendance = array_merge($params_attendance, $user_ids);
    }
    
    $stmt_attendance = $pdo->prepare($sql_attendance);
    $stmt_attendance->execute($params_attendance);
    $attendance_rows = $stmt_attendance->fetchAll();

    // ここからマージ処理
    $merged_records = [];

    // 予定シフトをマージ
    foreach ($scheduled_rows as $row) {
        $user_id = $row['user_id'];
        $date = $row['shift_date'];
        if (!isset($merged_records[$user_id])) {
            $merged_records[$user_id] = [];
        }
        if (!isset($merged_records[$user_id][$date])) {
            $merged_records[$user_id][$date] = [];
        }
        // 予定シフトを配列に追加
        $merged_records[$user_id][$date][] = [
            'start' => $row['start_time'],
            'end' => $row['end_time'],
            'type' => 'scheduled'
        ];
    }

    // 実績をマージ（実績がある場合は予定を上書き）
    foreach ($attendance_rows as $row) {
        $user_id = $row['user_id'];
        $date = $row['shift_date'];
        if (!isset($merged_records[$user_id])) {
            $merged_records[$user_id] = [];
        }
        if (!isset($merged_records[$user_id][$date])) {
            $merged_records[$user_id][$date] = [];
        }
        // 実績を配列に追加
        $merged_records[$user_id][$date][] = [
            'start' => $row['start_time'],
            'end' => $row['end_time'],
            'type' => 'actual'
        ];
    }

    return $merged_records;
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

/**
 * ユニークな招待コード（企業コード）を生成する
 * @return string
 */
function generate_company_code() {
    // 英数字ランダム8文字
    return strtoupper(substr(bin2hex(random_bytes(4)), 0, 8));
}

/**
 * 招待コードを検証し、有効であれば company_id を返す
 * @param PDO $pdo
 * @param string $code
 * @return int|false
 */
function validate_company_code($pdo, $code) {
    $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE company_code = ?");
    $stmt->execute([$code]);
    return $stmt->fetchColumn(); 
}

/**
 * 現在のコンテキスト（セッション等）から Company ID を取得する
 * ログインしていない場合は null を返す
 */
function get_current_company_id() {
    return $_SESSION['company_id'] ?? null;
}

/**
 * パスワードポリシーを検証する
 * @param string $password
 * @param int $company_id
 * @return true|string true if valid, error message string if invalid
 */
function validate_password_policy($password, $company_id) {
    if (empty($password)) return 'パスワードを入力してください。';
    
    $pdo = getPDO();
    // デフォルト値: 最小8文字, 複雑性不要
    $min_len = (int)SystemSetting::get($pdo, 'password_min_length', 8, $company_id);
    $require_complex = (bool)SystemSetting::get($pdo, 'password_require_complex', 0, $company_id);
    
    if (strlen($password) < $min_len) {
        return "パスワードは{$min_len}文字以上で設定してください。";
    }
    
    if ($require_complex) {
        // 英字(a-zA-Z), 数字(0-9), 記号(それ以外) の3種が含まれているか簡易チェック
        if (!preg_match('/[a-zA-Z]/', $password) || 
            !preg_match('/[0-9]/', $password) || 
            !preg_match('/[^a-zA-Z0-9]/', $password)) { 
            return 'パスワードは英字・数字・記号をそれぞれ1つ以上含める必要があります。';
        }
    }
    
    return true;
}






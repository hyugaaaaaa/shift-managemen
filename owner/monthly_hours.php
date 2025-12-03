<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー（管理者）権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();

// 対象月の取得
$selected_month = $_GET['month'] ?? date('Y-m');

// 締め日設定の取得
$closing_day = (int)get_system_setting($pdo, 'closing_day', 15);

// 集計期間の計算
$year = (int)substr($selected_month, 0, 4);
$month = (int)substr($selected_month, 5, 2);

// 締め日が15日の場合、対象月が 2023-11 なら、期間は 10/16 ～ 11/15
$start_date_obj = new DateTime("$year-$month-$closing_day");
$start_date_obj->modify('-1 month');
$start_date_obj->modify('+1 day');
$start_date = $start_date_obj->format('Y-m-d');

$end_date_obj = new DateTime("$year-$month-$closing_day");
$end_date = $end_date_obj->format('Y-m-d');

// ユーザー一覧取得
$stmt = $pdo->prepare('SELECT user_id, username, hourly_rate, transportation_expense FROM users WHERE user_type = ? AND is_deleted = 0 ORDER BY username');
$stmt->execute(['part-time']);
$users = $stmt->fetchAll();

// 勤務実績データ取得（予定と実績のマージ）
$merged_records = get_merged_work_records($pdo, $start_date, $end_date);

// 集計処理
$user_stats = [];
// 全ユーザーの初期化
foreach($users as $u) {
    $user_stats[$u['user_id']] = [
        'user' => $u,
        'normal_minutes' => 0, // 通常勤務時間（分）
        'night_minutes' => 0,  // 深夜勤務時間（分）
        'days_worked' => 0,    // 出勤日数
        'total_pay' => 0       // 総支給額
    ];
}

foreach ($merged_records as $uid => $dates) {
    if (!isset($user_stats[$uid])) continue;

    foreach ($dates as $date => $date_records) {
        // $date_records はその日のシフト/実績のリスト（配列）
        foreach ($date_records as $record) {
            // 勤務時間がない（欠勤など）場合はスキップ
            if (empty($record['start']) || empty($record['end'])) continue;

            // 日数は「勤務した日」としてカウントしたい場合、1日1回だけカウントすべきだが、
            // ここでは簡易的に「シフト回数」ではなく「出勤日数」としたい場合、
            // ループの外で日付ごとにカウントする必要がある。
            // しかし既存ロジックは単純なインクリメントだったため、仕様を確認する必要がある。
            // 一般的には「1日に2回シフトがあっても出勤日数は1日」とするのが自然。
            // ここでは一旦、このループ内では時間を加算し、日数はループの外（日付単位）でカウントするように修正する。
        }
        
        // その日に有効な勤務記録が1つでもあれば出勤日数を+1
        $has_worked_today = false;
        foreach ($date_records as $record) {
            if (!empty($record['start']) && !empty($record['end'])) {
                $has_worked_today = true;
                // 時間計算
                $times = calculate_shift_minutes($date, $record['start'], $record['end']);
                $user_stats[$uid]['night_minutes'] += $times['night_minutes'];
                $user_stats[$uid]['normal_minutes'] += $times['normal_minutes'];
            }
        }
        
        if ($has_worked_today) {
            $user_stats[$uid]['days_worked']++;
        }
    }
}

// ビューの読み込み
require_once __DIR__ . '/../views/owner/monthly_hours_view.php';

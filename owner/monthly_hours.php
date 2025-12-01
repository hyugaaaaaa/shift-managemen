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
$stmt = $pdo->prepare('SELECT user_id, username, hourly_rate, transportation_expense FROM users WHERE user_type = ? ORDER BY username');
$stmt->execute(['part-time']);
$users = $stmt->fetchAll();

// シフトデータ取得
$stmt = $pdo->prepare('
    SELECT * FROM shifts_scheduled 
    WHERE shift_date BETWEEN ? AND ?
    ORDER BY shift_date, start_time
');
$stmt->execute([$start_date, $end_date]);
$shifts = $stmt->fetchAll();

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

foreach($shifts as $s) {
    if (!isset($user_stats[$s['user_id']])) continue;
    
    $user_stats[$s['user_id']]['days_worked']++;
    
    // 共通関数で時間計算
    $times = calculate_shift_minutes($s['shift_date'], $s['start_time'], $s['end_time']);
    
    $user_stats[$s['user_id']]['night_minutes'] += $times['night_minutes'];
    $user_stats[$s['user_id']]['normal_minutes'] += $times['normal_minutes'];
}

// ビューの読み込み
require_once __DIR__ . '/../views/owner/monthly_hours_view.php';

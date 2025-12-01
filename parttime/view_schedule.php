<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// ログイン確認
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

// パートタイム以外はリダイレクト
if (!empty($_SESSION['user_type']) && $_SESSION['user_type'] !== 'part-time') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$stmt = $pdo->prepare('SELECT schedule_id, shift_date, start_time, end_time, created_at FROM shifts_scheduled WHERE user_id = ? ORDER BY shift_date ASC');
$stmt->execute([$_SESSION['user_id']]);
$schedules = $stmt->fetchAll();

// 表示用に日跨ぎシフトを分割して展開
$display_schedules = [];
foreach ($schedules as $s) {
  $start = substr($s['start_time'],0,8);
  $end = substr($s['end_time'],0,8);
  if (strtotime($end) <= strtotime($start)) {
    // 当日分
    $d1 = $s;
    $d1['shift_date'] = $s['shift_date'];
    $d1['display_start'] = substr($start,0,5);
    $d1['display_end'] = '24:00';
    $d1['note'] = '(翌日まで)';
    $display_schedules[] = $d1;
    // 翌日分
    $d2 = $s;
    $d2['shift_date'] = date('Y-m-d', strtotime($s['shift_date'].' +1 day'));
    $d2['display_start'] = '00:00';
    $d2['display_end'] = substr($end,0,5);
    $d2['note'] = '(前日から)';
    $display_schedules[] = $d2;
  } else {
    $s['display_start'] = substr($start,0,5);
    $s['display_end'] = substr($end,0,5);
    $s['note'] = '';
    $display_schedules[] = $s;
  }
}

// ビューの読み込み
require_once __DIR__ . '/../views/parttime/view_schedule_view.php';

<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/template.php';

// ログイン確認
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

// 表示対象の年月を取得（デフォルトは現在年月）
$year = intval($_GET['y'] ?? date('Y'));
$month = intval($_GET['m'] ?? date('n'));
if ($month < 1) { $month = 1; }
if ($month > 12) { $month = 12; }

// 月初と月末の日付を計算
$startOfMonth = date('Y-m-01', strtotime("{$year}-{$month}-01"));
$endOfMonth = date('Y-m-t', strtotime($startOfMonth));

$pdo = getPDO();
// シフトデータの取得
if (!empty($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner') {
    // オーナーの場合: 全従業員のシフトを取得
    $sql = 'SELECT s.*, u.username FROM shifts_scheduled s JOIN users u ON s.user_id = u.user_id WHERE s.shift_date BETWEEN ? AND ? ORDER BY s.shift_date, s.start_time';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$startOfMonth, $endOfMonth]);
} else {
    // パートタイムの場合: 自分のシフトのみ取得
    $sql = 'SELECT s.* FROM shifts_scheduled s WHERE s.user_id = ? AND s.shift_date BETWEEN ? AND ? ORDER BY s.shift_date, s.start_time';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $startOfMonth, $endOfMonth]);
}
$rows = $stmt->fetchAll();

// 日付ごとの配列に整理
$shifts_by_date = [];
foreach ($rows as $r) {
  $d = $r['shift_date'];
  $start = substr($r['start_time'],0,8);
  $end = substr($r['end_time'],0,8);
  
  // 日跨ぎ（終業時間が開始時間と同じか前）を判定
  if (strtotime($end) <= strtotime($start)) {
    // 当日分：出勤時間のみ表示（例: 出:22:00）
    $r1 = $r;
    $r1['display_start'] = substr($start,0,5);
    $r1['display_end'] = '';
    $r1['note'] = '(出勤)';
    if (!isset($shifts_by_date[$d])) $shifts_by_date[$d] = [];
    $shifts_by_date[$d][] = $r1;

    // 翌日分：退勤時間のみ表示（例: 退:02:00）
    $nextDate = date('Y-m-d', strtotime($d . ' +1 day'));
    $r2 = $r;
    $r2['display_start'] = '';
    $r2['display_end'] = substr($end,0,5);
    $r2['note'] = '(退勤)';
    if (!isset($shifts_by_date[$nextDate])) $shifts_by_date[$nextDate] = [];
    $shifts_by_date[$nextDate][] = $r2;
  } else {
    // 通常シフト
    $r['display_start'] = substr($start,0,5);
    $r['display_end'] = substr($end,0,5);
    if (!isset($shifts_by_date[$d])) $shifts_by_date[$d] = [];
    $shifts_by_date[$d][] = $r;
  }
}

// 前月・次月
$prev = date('Y-n', strtotime($startOfMonth.' -1 month'));
$next = date('Y-n', strtotime($startOfMonth.' +1 month'));
list($py, $pm) = explode('-', $prev);
list($ny, $nm) = explode('-', $next);

// ビューの読み込み
require_once __DIR__ . '/views/dashboard_view.php';

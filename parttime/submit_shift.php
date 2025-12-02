<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

if(empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'part-time'){
  header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
// 初期値の準備（フォーム再表示時などに使用）
$shift_date = $_POST['shift_date'] ?? date('Y-m-d');
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

// 提出期限チェック
$deadline_day = (int)get_system_setting($pdo, 'shift_submission_deadline_day', 25);
$today_day = (int)date('d');
$is_past_deadline = $today_day > $deadline_day;
$deadline_msg = "今月のシフト提出締め切りは {$deadline_day}日 です。";

if ($is_past_deadline) {
    $error = "今月のシフト提出期限（{$deadline_day}日）を過ぎているため、提出できません。";
}

// フォームが送信された場合の処理
if($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_past_deadline){
  // 入力値の取得
  $shift_date = $_POST['shift_date'] ?? '';
  $start_time = $_POST['start_time'] ?? '';
  $end_time = $_POST['end_time'] ?? '';
  
  // CSRFチェック
  if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
      $error = 'セッションが無効です。もう一度お試しください。';
  }
  // バリデーション: 必須項目チェック
  elseif(!$shift_date || !$start_time || !$end_time){
    $error = '日付と開始・終了時刻を入力してください。';
  } else {
    // データベースへの登録処理
    // request_status は 'pending'（承認待ち）として登録
    $stmt = $pdo->prepare('INSERT INTO shifts_requested (user_id, shift_date, start_time, end_time, request_status) VALUES (?, ?, ?, ?, ? )');
    $stmt->execute([$_SESSION['user_id'], $shift_date, $start_time, $end_time, 'pending']);
    
    $success = '希望シフトを登録しました。';
    
    // 登録成功後はフォームをクリア
    $shift_date = date('Y-m-d');
    $start_time = '';
    $end_time = '';
  }
}

// ビューの読み込み
require_once __DIR__ . '/../views/parttime/submit_shift_view.php';

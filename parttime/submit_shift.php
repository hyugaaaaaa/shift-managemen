<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

if(empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'part-time'){
  header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
// 翌月の計算（提出対象月）
$next_month_timestamp = strtotime('+1 month');
$target_year_month = date('Y年n月', $next_month_timestamp);
$min_date = date('Y-m-01', $next_month_timestamp);
$max_date = date('Y-m-t', $next_month_timestamp);

// 初期値の準備（フォーム再表示時などに使用）
$shift_date = $_POST['shift_date'] ?? $min_date;
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

// 提出期限チェック
$deadline_day = (int)get_system_setting($pdo, 'shift_submission_deadline_day', 25);
$today_day = (int)date('d');
$is_past_deadline = $today_day > $deadline_day;
$deadline_msg = "{$target_year_month}分のシフト提出締め切りは、今月{$deadline_day}日 です。";

// 定休日情報の取得
$stmt = $pdo->prepare("SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN ? AND ?");
$stmt->execute([$min_date, $max_date]);
$holidays = $stmt->fetchAll(PDO::FETCH_COLUMN);

// 定休日案内メッセージ作成
$holiday_msg = '';
if (!empty($holidays)) {
    $holiday_dates_str = [];
    foreach ($holidays as $h_date) {
        $holiday_dates_str[] = date('j', strtotime($h_date)) . '日' . get_day_of_week_ja($h_date);
    }
    $holiday_msg = "今月の定休日: " . implode(', ', $holiday_dates_str);
}

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
  }
  // バリデーション: 対象月チェック
  elseif ($shift_date < $min_date || $shift_date > $max_date) {
      $error = "{$target_year_month}（{$min_date} 〜 {$max_date}）の日付を指定してください。";
  } 
  // バリデーション: 定休日チェック
  elseif (in_array($shift_date, $holidays)) {
      $error = "指定された日付（{$shift_date}）は定休日のため、シフトを提出できません。";
  } else {
    // 重複チェック
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM shifts_requested WHERE user_id = ? AND shift_date = ? AND start_time = ? AND end_time = ?");
    // 時刻フォーマットを合わせるために秒まで指定するか、LIKE検索にするか検討が必要だが、
    // HTML5のtime inputは通常 HH:MM を返すが、DBが HH:MM:SS の場合を考慮して、
    // ここでは入力値に秒(:00)を付加して比較するか、DB側で比較する。
    // 一般的には time型カラムへのINSERT時は HH:MM でも通るが、SELECT時は HH:MM:SS で返ることが多い。
    // ここでは厳密な一致を見るため、入力値が HH:MM なら HH:MM:00 として扱うなどの正規化を行うのが安全だが、
    // 簡易的に、入力値をそのままバインドして、DB側でキャスト比較させる（MySQLは自動でよしなにやってくれることが多い）。
    // ただし、念のため入力値に秒がない場合は付与する処理を入れるとより確実。
    
    $check_start = (strlen($start_time) === 5) ? $start_time . ':00' : $start_time;
    $check_end = (strlen($end_time) === 5) ? $end_time . ':00' : $end_time;

    $stmt->execute([$_SESSION['user_id'], $shift_date, $check_start, $check_end]);
    if ($stmt->fetchColumn() > 0) {
        $error = '指定されたシフトは既に提出済みです。';
    } else {
        // データベースへの登録処理
        // request_status は 'pending'（承認待ち）として登録
        $stmt = $pdo->prepare('INSERT INTO shifts_requested (user_id, shift_date, start_time, end_time, request_status) VALUES (?, ?, ?, ?, ? )');
        $stmt->execute([$_SESSION['user_id'], $shift_date, $start_time, $end_time, 'pending']);
        
        $success = '希望シフトを登録しました。';
        
        // 登録成功後はフォームをクリア
        $shift_date = $min_date;
        $start_time = '';
        $end_time = '';
    }
  }
}

// ビューの読み込み
require_once __DIR__ . '/../views/parttime/submit_shift_view.php';

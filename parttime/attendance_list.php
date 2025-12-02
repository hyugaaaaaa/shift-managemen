<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// アルバイト権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'part-time') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// フォーム送信処理（実績の登録・修正）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = '不正なリクエストです。';
    } else {
        $date = $_POST['date'] ?? '';
        $clock_in = $_POST['clock_in'] ?? '';
        $clock_out = $_POST['clock_out'] ?? '';
        $status = $_POST['status'] ?? 'present';
        $notes = $_POST['notes'] ?? '';
        
        if ($date) {
            try {
                // 既存レコード確認
                $stmt = $pdo->prepare("SELECT attendance_id FROM attendance_records WHERE user_id = ? AND date = ?");
                $stmt->execute([$user_id, $date]);
                $existing = $stmt->fetch();
                
                $clock_in_dt = $clock_in ? "$date $clock_in" : null;
                $clock_out_dt = $clock_out ? "$date $clock_out" : null;
                
                // 日またぎ対応（簡易）: 退勤が出勤より前なら翌日扱い
                if ($clock_in_dt && $clock_out_dt && $clock_out < $clock_in) {
                    $clock_out_dt = date('Y-m-d H:i', strtotime("$date $clock_out +1 day"));
                }

                if ($existing) {
                    // 更新
                    $stmt = $pdo->prepare("UPDATE attendance_records SET clock_in_time = ?, clock_out_time = ?, status = ?, notes = ?, is_approved = 0 WHERE attendance_id = ?");
                    $stmt->execute([$clock_in_dt, $clock_out_dt, $status, $notes, $existing['attendance_id']]);
                    $message = '勤怠実績を更新しました。オーナーの承認をお待ちください。';
                } else {
                    // 新規作成
                    // 対応するシフト予定IDを取得（あれば）
                    $stmt = $pdo->prepare("SELECT schedule_id FROM shifts_scheduled WHERE user_id = ? AND shift_date = ?");
                    $stmt->execute([$user_id, $date]);
                    $sched = $stmt->fetch();
                    $schedule_id = $sched ? $sched['schedule_id'] : null;

                    $stmt = $pdo->prepare("INSERT INTO attendance_records (user_id, schedule_id, date, clock_in_time, clock_out_time, status, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$user_id, $schedule_id, $date, $clock_in_dt, $clock_out_dt, $status, $notes]);
                    $message = '勤怠実績を登録しました。';
                }
            } catch (PDOException $e) {
                $error = 'エラーが発生しました: ' . $e->getMessage();
            }
        }
    }
}

// 表示対象月
$month = $_GET['month'] ?? date('Y-m');
$start_date = "$month-01";
$end_date = date('Y-m-t', strtotime($start_date));

// 勤務実績取得
$work_records = get_merged_work_records($pdo, $start_date, $end_date, $user_id);
$my_records = $work_records[$user_id] ?? [];

// カレンダー表示用に日付リスト作成
$dates = [];
$d = new DateTime($start_date);
$e = new DateTime($end_date);
while ($d <= $e) {
    $dates[] = $d->format('Y-m-d');
    $d->modify('+1 day');
}

require_once __DIR__ . '/../views/parttime/attendance_list_view.php';

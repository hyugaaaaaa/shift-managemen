<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$message = '';
$error = '';

// フォーム送信処理（承認・修正）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = '不正なリクエストです。';
    } else {
        $action = $_POST['action'] ?? '';
        $attendance_id = $_POST['attendance_id'] ?? '';
        
        if ($action === 'approve' && $attendance_id) {
            $stmt = $pdo->prepare("UPDATE attendance_records SET is_approved = 1 WHERE attendance_id = ?");
            $stmt->execute([$attendance_id]);
            $message = '承認しました。';
        } elseif ($action === 'update' && $attendance_id) {
            // オーナーによる直接修正
            $clock_in = $_POST['clock_in'] ?? null;
            $clock_out = $_POST['clock_out'] ?? null;
            $status = $_POST['status'] ?? 'present';
            $notes = $_POST['notes'] ?? '';
            
            // 日付を取得して日時文字列を再構築する必要があるが、
            // 簡易的にPOSTデータには日時（Y-m-d H:i）が来ると仮定するか、
            // あるいは日付フィールドもhiddenで送るか。
            // ここではモーダルから Y-m-d H:i 形式で送るようにJS側で制御する方針とする。
            // しかしinput type="time"だと日付がつかない。
            // 元のレコードの日付を取得して結合するのが安全。
            
            $stmt = $pdo->prepare("SELECT date FROM attendance_records WHERE attendance_id = ?");
            $stmt->execute([$attendance_id]);
            $rec = $stmt->fetch();
            
            if ($rec) {
                $date = $rec['date'];
                $clock_in_dt = $clock_in ? "$date $clock_in" : null;
                $clock_out_dt = $clock_out ? "$date $clock_out" : null;
                
                if ($clock_in_dt && $clock_out_dt && $clock_out < $clock_in) {
                    $clock_out_dt = date('Y-m-d H:i', strtotime("$date $clock_out +1 day"));
                }
                
                $stmt = $pdo->prepare("UPDATE attendance_records SET clock_in_time = ?, clock_out_time = ?, status = ?, notes = ?, is_approved = 1 WHERE attendance_id = ?");
                $stmt->execute([$clock_in_dt, $clock_out_dt, $status, $notes, $attendance_id]);
                $message = '修正して承認しました。';
            }
        }
    }
}

// 表示対象月
$month = $_GET['month'] ?? date('Y-m');
$start_date = "$month-01";
$end_date = date('Y-m-t', strtotime($start_date));

// ユーザー一覧取得
$stmt = $pdo->prepare('SELECT user_id, username FROM users WHERE user_type = ? AND is_deleted = 0 ORDER BY username');
$stmt->execute(['part-time']);
$users = $stmt->fetchAll();

// 全員の勤務実績取得
$all_records = get_merged_work_records($pdo, $start_date, $end_date);

require_once __DIR__ . '/../views/owner/attendance_approval_view.php';

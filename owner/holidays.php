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
$msg = '';
$error = '';

// 対象月の取得（デフォルトは翌月）
$month = $_GET['month'] ?? date('Y-m', strtotime('+1 month'));

// 月のバリデーション
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    $month = date('Y-m', strtotime('+1 month'));
}

// 保存処理
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        $selected_dates = $_POST['holidays'] ?? [];
        
        try {
            $pdo->beginTransaction();
            
            // 対象月の既存の定休日を削除
            $start_date = $month . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $stmt = $pdo->prepare("DELETE FROM holidays WHERE holiday_date BETWEEN ? AND ?");
            $stmt->execute([$start_date, $end_date]);
            
            // 新しい定休日を登録
            if (!empty($selected_dates)) {
                $stmt = $pdo->prepare("INSERT INTO holidays (holiday_date, description) VALUES (?, '定休日')");
                foreach ($selected_dates as $date) {
                    // 日付の形式チェック
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        $stmt->execute([$date]);
                    }
                }
            }
            
            $pdo->commit();
            $msg = '定休日設定を保存しました。';
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = '保存に失敗しました: ' . $e->getMessage();
        }
    }
}

// 現在の定休日データを取得
$start_date = $month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));
$stmt = $pdo->prepare("SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN ? AND ?");
$stmt->execute([$start_date, $end_date]);
$current_holidays = $stmt->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../views/owner/holidays_view.php';

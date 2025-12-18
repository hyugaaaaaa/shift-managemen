<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

use App\Models\SystemSetting;

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
        $selected_dates = $_POST['holidays'] ?? []; // 臨時定休 (特定日)
        $regular_days = $_POST['regular_days'] ?? []; // 曜日定休 (0-6)
        
        try {
            $pdo->beginTransaction();
            $company_id = get_current_company_id();

            // 1. 曜日定休の保存 (system_settings)
            $regular_days_str = implode(',', $regular_days);
            SystemSetting::save($pdo, 'regular_holiday_days', $regular_days_str, 'weekly_holidays', $company_id);
            
            // 2. 臨時定休の保存
            // 対象月の既存の「臨時定休」を削除
            $start_date = $month . '-01';
            $end_date = date('Y-m-t', strtotime($start_date));
            
            $stmt = $pdo->prepare("DELETE FROM holidays WHERE holiday_date BETWEEN ? AND ? AND company_id = ?");
            $stmt->execute([$start_date, $end_date, $company_id]);
            
            // 新しい臨時定休を登録
            // ただし、曜日定休ですでに休みとなっている日は登録しないほうがデータが綺麗だが、
            // UI上チェックされているものは意図的に「この日は休み」と指定されたものとして保存しても問題はない。
            // 重複しても get_company_holidays で unique するので動作上はOK。
            // ここではシンプルにPOSTされたものを保存する。
            
            if (!empty($selected_dates)) {
                $stmt = $pdo->prepare("INSERT INTO holidays (company_id, holiday_date, description) VALUES (?, ?, '臨時定休')");
                foreach ($selected_dates as $date) {
                    if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                        $stmt->execute([$company_id, $date]);
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

// 現在の設定データを取得
$company_id = get_current_company_id();

// 1. 曜日定休
$regular_days_str = get_system_setting($pdo, 'regular_holiday_days', '');
$current_regular_days = ($regular_days_str !== '') ? explode(',', $regular_days_str) : [];

// 2. 臨時定休 (holidaysテーブル)
$start_date = $month . '-01';
$end_date = date('Y-m-t', strtotime($start_date));
$stmt = $pdo->prepare("SELECT holiday_date FROM holidays WHERE holiday_date BETWEEN ? AND ? AND company_id = ?");
$stmt->execute([$start_date, $end_date, $company_id]);
$current_holidays = $stmt->fetchAll(PDO::FETCH_COLUMN);

require_once __DIR__ . '/../views/owner/holidays_view.php';

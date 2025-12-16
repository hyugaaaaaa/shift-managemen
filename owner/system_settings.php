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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        $deadline_day = (int)($_POST['shift_submission_deadline_day'] ?? 25);
        $closing_day = (int)($_POST['closing_day'] ?? 31);
        $payment_day = (int)($_POST['payment_day'] ?? 25);
        $password_min_length = (int)($_POST['password_min_length'] ?? 8);
        $password_require_complex = isset($_POST['password_require_complex']) ? 1 : 0; // Checkbox

        if ($deadline_day < 1 || $deadline_day > 31 || 
            $closing_day < 1 || $closing_day > 31 || 
            $payment_day < 1 || $payment_day > 31) {
            $error = '日付は1から31の間で指定してください。';
        } elseif ($password_min_length < 4 || $password_min_length > 32) {
            $error = 'パスワードの最小文字数は4から32の間で指定してください。';
        } else {
            // 設定保存 (UPSERT)
            $company_id = get_current_company_id();
            $stmt = $pdo->prepare("INSERT INTO system_settings (company_id, setting_key, setting_value) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            
            $stmt->execute([$company_id, 'shift_submission_deadline_day', $deadline_day]);
            $stmt->execute([$company_id, 'closing_day', $closing_day]);
            $stmt->execute([$company_id, 'payment_day', $payment_day]);
            $stmt->execute([$company_id, 'password_min_length', $password_min_length]);
            $stmt->execute([$company_id, 'password_require_complex', $password_require_complex]);
            

            
            $msg = '設定を保存しました。';
        }
    }
}

// 現在の設定値取得
$current_deadline = get_system_setting($pdo, 'shift_submission_deadline_day', 25);
$current_closing_day = get_system_setting($pdo, 'closing_day', 31);
$current_payment_day = get_system_setting($pdo, 'payment_day', 25);
$current_password_min_length = get_system_setting($pdo, 'password_min_length', 8);
$current_password_require_complex = get_system_setting($pdo, 'password_require_complex', 0);

// 企業情報の取得
$company_code = '';
if (isset($_SESSION['company_id'])) {
    $stmt = $pdo->prepare("SELECT company_code FROM companies WHERE company_id = ?");
    $stmt->execute([$_SESSION['company_id']]);
    $company_code = $stmt->fetchColumn() ?: '';
}

require_once __DIR__ . '/../views/owner/system_settings_view.php';

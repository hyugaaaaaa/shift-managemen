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
        if ($deadline_day < 1 || $deadline_day > 31) {
            $error = '日付は1から31の間で指定してください。';
        } else {
            // 設定保存 (UPSERT)
            $stmt = $pdo->prepare("INSERT INTO system_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
            $stmt->execute(['shift_submission_deadline_day', $deadline_day]);
            $msg = '設定を保存しました。';
        }
    }
}

// 現在の設定値取得
$current_deadline = get_system_setting($pdo, 'shift_submission_deadline_day', 25);

require_once __DIR__ . '/../views/owner/system_settings_view.php';

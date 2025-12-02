<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/template.php';

// ログイン確認
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = '全ての項目を入力してください。';
    } elseif ($new_password !== $confirm_password) {
        $error = '新しいパスワードが一致しません。';
    } elseif (strlen($new_password) < 8) {
        $error = '新しいパスワードは8文字以上で設定してください。';
    } else {
        $pdo = getPDO();
        // 現在のパスワード確認
        $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $hash = $stmt->fetchColumn();

        if ($hash && password_verify($current_password, $hash)) {
            // パスワード更新
            $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password_hash = ? WHERE user_id = ?");
            $stmt->execute([$new_hash, $_SESSION['user_id']]);
            $msg = 'パスワードを変更しました。';
        } else {
            $error = '現在のパスワードが正しくありません。';
        }
    }
}

// ビューの読み込み
require_once __DIR__ . '/views/change_password_view.php';

<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

// session_start(); // template.phpで開始済みのため削除
$pdo = getPDO();
$error = '';
$success = '';
$valid_token = false;
$email = '';

// トークン検証
$token = $_GET['token'] ?? $_POST['token'] ?? '';

if (empty($token)) {
    $error = '無効なアクセスです。';
} else {
    $stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW()");
    $stmt->execute([$token]);
    $reset_request = $stmt->fetch();

    if ($reset_request) {
        $valid_token = true;
        $email = $reset_request['email'];
    } else {
        $error = 'このリンクは無効か、有効期限が切れています。もう一度申請し直してください。';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $valid_token) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。';
    } else {
        $password = $_POST['password'] ?? '';
        $password_confirm = $_POST['password_confirm'] ?? '';

        if (empty($password) || strlen($password) < 4) { // 仮の長さ制限
            $error = 'パスワードは4文字以上で入力してください。';
        } elseif ($password !== $password_confirm) {
            $error = 'パスワードが一致しません。';
        } else {
            // パスワード更新
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $up = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
            if ($up->execute([$hash, $email])) {
                // トークン削除
                $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
                $del->execute([$email]);
                
                $success = 'パスワードを再設定しました。';
                $valid_token = false; // フォーム非表示へ
            } else {
                $error = 'エラーが発生しました。';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
require_once __DIR__ . '/views/reset_password_view.php';

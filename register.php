<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/template.php';

$pdo = getPDO();

// 既存ユーザーチェック
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_deleted = 0");
$user_count = $stmt->fetchColumn();

if ($user_count > 0) {
    // 既にユーザーがいる場合は登録不可
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif ($username === '' || $password === '' || $confirm_password === '') {
        $error = '全ての項目を入力してください。';
    } elseif ($password !== $confirm_password) {
        $error = 'パスワードが一致しません。';
    } elseif (strlen($password) < 8) {
        $error = 'パスワードは8文字以上で設定してください。';
    } else {
        // ユーザー登録 (最初のユーザーなので owner 固定)
        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, user_type) VALUES (?, ?, 'owner')");
        try {
            $stmt->execute([$username, $password_hash]);
            header('Location: ' . BASE_PATH . '/index.php?registered=1');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = 'そのユーザー名は既に使用されています。';
            } else {
                $error = '登録中にエラーが発生しました。';
            }
        }
    }
}

require_once __DIR__ . '/views/register_view.php';

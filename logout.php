<?php
require_once __DIR__ . '/template.php';

// セッションの開始（既存セッションの再開）
session_start();

// セッション変数を全て解除
$_SESSION = [];

// セッションクッキーの削除
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// セッションの破棄
session_destroy();

// ログイン画面へリダイレクト
header('Location: index.php');
exit;

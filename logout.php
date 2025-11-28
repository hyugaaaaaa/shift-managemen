<?php
require_once __DIR__ . '/template.php';

// セッション破棄してログイン画面へリダイレクト
// template.php が session_start() を呼んでいるため、そのままセッション操作可能
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
}
session_destroy();
// 相対パスでリダイレクト
header('Location: index.php?logged_out=1');
exit;

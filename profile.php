<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/template.php';
require_once __DIR__ . '/functions.php';

// ログインチェック
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];
$error = '';
$msg = '';

// フォーム送信時の処理
// 現在は機能なし

// 現在の設定取得
// $stmt = $pdo->prepare("SELECT line_user_id FROM users WHERE user_id = ?");
// $stmt->execute([$user_id]);
// $user = $stmt->fetch();
$user = []; // ダミー


render_header('ユーザー設定');
require_once __DIR__ . '/views/profile_view.php';
render_footer();

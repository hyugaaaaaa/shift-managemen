<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー（管理者）権限チェック
// セッションがない、またはユーザータイプが 'owner' でない場合はログイン画面へリダイレクト
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
// パートタイム従業員の一覧を取得
// ユーザーID、名前、時給、交通費を取得し、名前順にソート
$stmt = $pdo->prepare('SELECT user_id, username, hourly_rate, transportation_expense FROM users WHERE user_type = ? ORDER BY username');
$stmt->execute(['part-time']);
$users = $stmt->fetchAll();

// ビューの読み込み
require_once __DIR__ . '/../views/owner/users_view.php';

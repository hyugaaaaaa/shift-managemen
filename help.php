<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/template.php';

// ログイン確認
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

// ヘッダーの出力
render_header('ヘルプ');

// ビューの読み込み
require_once __DIR__ . '/views/help_view.php';

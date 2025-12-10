<?php
// Composerのオートローダー読み込み
require_once __DIR__ . '/vendor/autoload.php';

// .envの読み込み（エラー制御付き）
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();
} catch (Exception $e) {
    // .envがなくても続行（本番環境での環境変数設定を想定）
}

// セッションセキュリティ設定の強化（セッション開始前に設定が必要）
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// DB接続設定
define('DB_HOST', $_ENV['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'shift_management');
define('DB_USER', $_ENV['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? '');

// アプリのベースパス
if(!defined('BASE_PATH')) define('BASE_PATH', $_ENV['BASE_PATH'] ?? '/shift_management');

// メール送信設定 (SMTP)
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? '');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? 587);
define('SMTP_USER', $_ENV['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASSWORD'] ?? '');
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? 'tls');
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? '');
define('FROM_NAME', $_ENV['FROM_NAME'] ?? 'Shift Management System');


// データベース接続関数
// シングルトンパターンで接続を再利用します
function getPDO(){
    static $pdo = null;
    if($pdo) return $pdo;
    
    // DSN (Data Source Name) の構築
    $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4';
    $opts = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // エラー時に例外を投げる
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // デフォルトで連想配列として取得
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $opts);
    return $pdo;
}

require_once __DIR__ . '/functions.php';

?>

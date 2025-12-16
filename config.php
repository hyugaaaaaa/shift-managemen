<?php
// Composerのオートローダー読み込み
require_once __DIR__ . '/vendor/autoload.php';

// .envの読み込み（エラー制御付き）
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
} catch (Exception $e) {
    // phpdotenvでの読み込みに失敗した場合のフォールバック (手動パース)
    // CLI環境などでエンコーディングやフォーマットの問題で失敗する場合があるため
    $envFile = __DIR__ . '/.env';
    if (file_exists($envFile)) {
        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '#') === 0) continue; // コメントスキップ
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                // 引用符の削除 (簡易的)
                $value = trim($value, "\"'");
                // 環境変数にセット
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// セッションセキュリティ設定の強化（セッション開始前に設定が必要）
ini_set('session.cookie_httponly', 1);
ini_set('session.use_strict_mode', 1);
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    ini_set('session.cookie_secure', 1);
}

// DB接続設定
define('DB_HOST', $_ENV['DB_HOST'] ?? $_SERVER['DB_HOST'] ?? '127.0.0.1');
define('DB_NAME', $_ENV['DB_NAME'] ?? $_SERVER['DB_NAME'] ?? 'shift_management');
define('DB_USER', $_ENV['DB_USER'] ?? $_SERVER['DB_USER'] ?? 'root');
define('DB_PASS', $_ENV['DB_PASSWORD'] ?? $_SERVER['DB_PASSWORD'] ?? $_ENV['DB_PASS'] ?? $_SERVER['DB_PASS'] ?? '');

// アプリのベースパス
if(!defined('BASE_PATH')) define('BASE_PATH', $_ENV['BASE_PATH'] ?? $_SERVER['BASE_PATH'] ?? '/shift_management');

// メール送信設定 (SMTP)
define('SMTP_HOST', $_ENV['SMTP_HOST'] ?? $_SERVER['SMTP_HOST'] ?? '');
define('SMTP_PORT', $_ENV['SMTP_PORT'] ?? $_SERVER['SMTP_PORT'] ?? 587);
define('SMTP_USER', $_ENV['SMTP_USER'] ?? $_SERVER['SMTP_USER'] ?? '');
define('SMTP_PASS', $_ENV['SMTP_PASSWORD'] ?? $_SERVER['SMTP_PASSWORD'] ?? '');
define('SMTP_SECURE', $_ENV['SMTP_SECURE'] ?? $_SERVER['SMTP_SECURE'] ?? 'tls');
define('FROM_EMAIL', $_ENV['FROM_EMAIL'] ?? $_SERVER['FROM_EMAIL'] ?? '');
define('FROM_NAME', $_ENV['FROM_NAME'] ?? $_SERVER['FROM_NAME'] ?? 'Shift Management System');

// ログインエラー詳細表示 (true: 詳細表示, false: 汎用メッセージ)
// セキュリティ上は false 推奨だが、社内システム等で利便性重視なら true
define('SHOW_DETAILED_LOGIN_ERRORS', false);

// PHP実行ファイルのパス (XAMPP環境等でパスが通っていない場合に対応)
define('PHP_BINARY_PATH', $_ENV['PHP_BINARY_PATH'] ?? $_SERVER['PHP_BINARY_PATH'] ?? 'C:\\xampp\\php\\php.exe');


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

<?php
// DB接続設定 - 本番では環境変数や別途設定ファイルで管理してください
define('DB_HOST','127.0.0.1');
define('DB_NAME','shift_management');
define('DB_USER','root');
define('DB_PASS','');
// アプリのベースパス（ドキュメントルート配下の配置フォルダ）。
// XAMPP で `c:\xampp\htdocs\shift_management` に置いている場合は '/shift_management' を指定。
// 開発環境に合わせて変更してください。
if(!defined('BASE_PATH')) define('BASE_PATH', '/shift_management');

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

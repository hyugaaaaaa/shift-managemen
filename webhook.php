<?php
require_once __DIR__ . '/config.php';

// エラーログ設定（デバッグ用）
ini_set('log_errors', 'On');
ini_set('error_log', __DIR__ . '/php_error.log');

$pdo = getPDO();

// 1. 署名検証
$channel_secret = get_system_setting($pdo, 'line_channel_secret', '');
if (empty($channel_secret)) {
    http_response_code(500);
    error_log('LINE Channel Secret is not set.');
    exit;
}

$http_request_body = file_get_contents('php://input');
$hash = hash_hmac('sha256', $http_request_body, $channel_secret, true);
$signature = base64_encode($hash);

$header_signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';

if (!hash_equals($signature, $header_signature)) {
    http_response_code(400);
    error_log('Invalid signature.');
    exit;
}

// 2. イベント処理
$json = json_decode($http_request_body, true);
if (empty($json['events'])) {
    http_response_code(200);
    exit;
}

foreach ($json['events'] as $event) {
    // メッセージイベント または フォローイベント
    if ($event['type'] === 'message' || $event['type'] === 'follow') {
        $user_id = $event['source']['userId'] ?? '';
        $reply_token = $event['replyToken'] ?? '';

        if ($user_id && $reply_token) {
            $message = "あなたのUser IDは以下です。\nコピーしてシステム設定に入力してください。\n\n" . $user_id;
            
            // 返信実行
            $result = reply_line_message($pdo, $reply_token, $message);
            
            if (!$result) {
                error_log('Failed to reply message.');
            }
        }
    }
}

http_response_code(200);

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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        $line_user_id = trim($_POST['line_user_id'] ?? '');
        
        try {
            $stmt = $pdo->prepare("UPDATE users SET line_user_id = ? WHERE user_id = ?");
            $stmt->execute([$line_user_id, $user_id]);
            $msg = '設定を保存しました。';
            
            // テスト送信
            if (!empty($line_user_id)) {
                if (send_line_push($pdo, $line_user_id, "LINE連携設定が完了しました。")) {
                    $msg .= ' テスト通知を送信しました。';
                } else {
                    $error = '設定は保存されましたが、テスト通知の送信に失敗しました。IDが正しいか、またはボットがブロックされていないか確認してください。';
                }
            }
            
        } catch (Exception $e) {
            $error = 'エラーが発生しました: ' . $e->getMessage();
        }
    }
}

// 現在の設定取得
$stmt = $pdo->prepare("SELECT line_user_id FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

render_header('ユーザー設定');
require_once __DIR__ . '/views/profile_view.php';
render_footer();

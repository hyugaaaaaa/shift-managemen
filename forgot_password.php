<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFチェック (template.phpでsession_start済み前提だが、ここはログイン前なので個別にstartが必要か確認。
    // template.phpは共通ヘッダ出力用なので、ここでは独自にsession_startする)
    // session_start(); // template.phpで開始済みのため削除
    
    $email = $_POST['email'] ?? '';
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
         $error = 'セッションが無効です。';
    } elseif (empty($email)) {
        $error = 'メールアドレスを入力してください。';
    } else {
        $pdo = getPDO();
        // メールアドレス存在確認
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND is_deleted = 0");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            // トークン生成
            $token = bin2hex(random_bytes(32));
            $expires_at = date('Y-m-d H:i:s', strtotime('+24 hours'));
            
            // 既存のトークンがあれば無効化(削除)して新規作成
            $del = $pdo->prepare("DELETE FROM password_resets WHERE email = ?");
            $del->execute([$email]);
            
            $ins = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            $ins->execute([$email, $token, $expires_at]);
            
            // メール送信
            require_once __DIR__ . '/functions.php'; // send_mail用
            $url = BASE_PATH . '/reset_password.php?token=' . $token;
            $subject = '【シフト管理システム】パスワード再設定のご案内';
            $body = "パスワード再設定のリクエストを受け付けました。\n以下のリンクから24時間以内に新しいパスワードを設定してください。\n\n" . $url . "\n\n※心当たりがない場合はこのメールを無視してください。";
            
            if (send_mail($email, $subject, $body)) {
                $success = 'パスワード再設定用のメールを送信しました。';
            } else {
                $error = 'メール送信に失敗しました。';
            }
        } else {
            // セキュリティ上、登録なしでも送信したふりをするか、エラーを出すか。
            // 計画通り「送信しました」とする（本来はあいまいにすべきだが、ここは分かりやすく成功表示にするか、
            // あるいは「登録されていないアドレスです」と出すか。計画では『送信しました（存在する場合）』とあるので、
            // 実装上は、存在しない場合は何もせず「送信しました」と出すのが安全。）
            $success = 'パスワード再設定用のメールを送信しました。'; 
        }
    }
} else {
    // session_start(); // template.phpで開始済みのため削除
}

$csrf_token = generate_csrf_token();

require_once __DIR__ . '/views/forgot_password_view.php';

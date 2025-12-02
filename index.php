<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

// ログイン処理
$pdo = getPDO();

// 新規登録リンク表示判定
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE is_deleted = 0");
$user_count = $stmt->fetchColumn();
$show_register_link = ($user_count == 0);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if($username === '' || $password === ''){
        $error = 'ユーザー名とパスワードを入力してください。';
    } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        // ユーザー名でDBを検索
        $stmt = $pdo->prepare('SELECT user_id, username, password_hash, user_type, login_attempts, locked_until FROM users WHERE username = ? AND is_deleted = 0 LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // アカウントロックチェック
        if ($user && $user['locked_until'] && new DateTime($user['locked_until']) > new DateTime()) {
            $error = 'アカウントがロックされています。しばらく待ってから再試行してください。';
        } else {
            // パスワードのハッシュ検証
            if($user && password_verify($password, $user['password_hash'])){
                // 認証成功: ロック解除
                $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);

                // セッション固定攻撃対策
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['last_activity'] = time(); // セッション開始時刻
                
                header('Location: dashboard.php');
                exit;
            } else {
                // 認証失敗: 試行回数カウントアップ
                if ($user) {
                    $attempts = $user['login_attempts'] + 1;
                    $locked_until = null;
                    $error = '認証に失敗しました。ユーザー名・パスワードを確認してください。';

                    // 5回失敗でロック (30分)
                    if ($attempts >= 5) {
                        $locked_until = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');
                        $error = 'ログイン失敗回数が上限を超えました。アカウントを30分間ロックします。';
                    }
                    
                    $stmt = $pdo->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE user_id = ?");
                    $stmt->execute([$attempts, $locked_until, $user['user_id']]);
                } else {
                     $error = '認証に失敗しました。ユーザー名・パスワードを確認してください。';
                }
            }
        }
    }
}

// ビューの読み込み
require_once __DIR__ . '/views/login_view.php';

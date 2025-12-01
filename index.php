<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

// ログイン処理
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if($username === '' || $password === ''){
        $error = 'ユーザー名とパスワードを入力してください。';
    } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        $pdo = getPDO();
        // ユーザー名でDBを検索（SQLインジェクション対策のためプリペアドステートメントを使用）
        $stmt = $pdo->prepare('SELECT user_id, username, password_hash, user_type FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        // パスワードのハッシュ検証
        if($user && password_verify($password, $user['password_hash'])){
            // 認証成功: セッション固定攻撃対策のためにセッションIDを再生成
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            
            // ログイン直後はダッシュボード（月表示のシフト表）へ遷移
            header('Location: dashboard.php');
            exit;
        } else {
            $error = '認証に失敗しました。ユーザー名・パスワードを確認してください。';
        }
    }
}

// ビューの読み込み
require_once __DIR__ . '/views/login_view.php';

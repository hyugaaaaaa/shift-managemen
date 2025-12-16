<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

// ログイン処理
// ログイン処理
$pdo = getPDO();

// 保存された企業コードを取得
$saved_company_code = $_COOKIE['saved_company_code'] ?? '';

// 新規登録リンク表示判定 (旧ロジック: ユーザー0人なら表示 -> 撤廃)
// 常にログイン画面を表示し、下部のリンクから登録へ誘導するスタイルへ変更。
// よって $show_register_link 変数は不要化、あるいは常に false (View側で制御済み)

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $company_code = trim($_POST['company_code'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if($company_code === '' || $username === '' || $password === ''){
        $error = '企業コード、ユーザー名、パスワードを入力してください。';
    } elseif (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        // 1. 企業コードから Company ID を取得
        $stmt = $pdo->prepare("SELECT company_id FROM companies WHERE company_code = ?");
        $stmt->execute([$company_code]);
        $company_id = $stmt->fetchColumn();

        if (!$company_id) {
            // セキュリティ向上のため、企業コード間違いも「認証失敗」として曖昧にするのが一般的だが、
            // ユーザビリティのため「企業コードが見つかりません」と出しても良い（社内システム等なら）。
            if (defined('SHOW_DETAILED_LOGIN_ERRORS') && SHOW_DETAILED_LOGIN_ERRORS) {
                 $error = '指定された企業コードが見つかりません。';
            } else {
                 $error = '認証に失敗しました。入力内容を確認してください。';
            }
        } else {
            // 2. ユーザー名と Company ID でユーザーを特定
            // 論理削除されていないユーザーのみ対象
            $stmt = $pdo->prepare('SELECT user_id, username, password_hash, user_type, login_attempts, locked_until, company_id FROM users WHERE username = ? AND company_id = ? AND is_deleted = 0');
            $stmt->execute([$username, $company_id]);
            $user = $stmt->fetch();
            
            if ($user) {
                // アカウントロックチェック
                if ($user['locked_until'] && new DateTime($user['locked_until']) > new DateTime()) {
                    $error = 'アカウントがロックされています。しばらく待ってから再試行してください。';
                } else {
                    // パスワード検証
                    if (password_verify($password, $user['password_hash'])) {
                        // 認証成功
                        
                        // ロック解除リセット
                        $stmt = $pdo->prepare("UPDATE users SET login_attempts = 0, locked_until = NULL WHERE user_id = ?");
                        $stmt->execute([$user['user_id']]);

                        // セッション固定攻撃対策
                        session_regenerate_id(true);
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['username'] = $user['username'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['company_id'] = $user['company_id'];
                        $_SESSION['company_id'] = $user['company_id'];
                        $_SESSION['last_activity'] = time();
                        
                        // 企業コードをCookieに保存 (6ヶ月)
                        setcookie('saved_company_code', $company_code, time() + 60*60*24*180, '/', '', false, true);
                        
                        header('Location: dashboard.php');
                        exit;
                    } else {
                        // パスワード不一致 -> ロックカウント加算
                        $attempts = $user['login_attempts'] + 1;
                        $locked_until = null;
                        $error = '認証に失敗しました。ユーザー名・パスワードを確認してください。';

                        if ($attempts >= 5) {
                            $locked_until = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');
                            $error = 'ログイン失敗回数が上限を超えました。アカウントを30分間ロックします。';
                        }
                        
                        $stmt = $pdo->prepare("UPDATE users SET login_attempts = ?, locked_until = ? WHERE user_id = ?");
                        $stmt->execute([$attempts, $locked_until, $user['user_id']]);
                    }
                }
            } else {
                // ユーザーが見つからない
                // タイミング攻撃対策でダミーのハッシュ検証などを本来は入れるべきだが、ここでは簡易的実装
                if (defined('SHOW_DETAILED_LOGIN_ERRORS') && SHOW_DETAILED_LOGIN_ERRORS) {
                     $error = 'ユーザー名が見つかりません。';
                } else {
                     $error = '認証に失敗しました。入力内容を確認してください。';
                }
            }
        }
    }
}

// ビューの読み込み
require_once __DIR__ . '/views/login_view.php';

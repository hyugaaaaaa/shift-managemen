<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

$pdo = getPDO();
$error = '';
$success = '';
$csrf_token = generate_csrf_token();
$companies = []; // 複数企業選択用

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $company_id = $_POST['company_id'] ?? null;
    $token = $_POST['csrf_token'] ?? '';

    if (!validate_csrf_token($token)) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif (empty($email)) {
        $error = 'メールアドレスを入力してください。';
    } else {
        // 会社が指定されているか、まだか
        if ($company_id) {
            // 会社指定あり：リセット処理実行
            $stmt = $pdo->prepare("SELECT user_id FROM users WHERE email = ? AND company_id = ? AND is_deleted = 0");
            $stmt->execute([$email, $company_id]);
            $user = $stmt->fetch();

            if ($user) {
                // トークン生成と保存
                $reset_token = bin2hex(random_bytes(32));
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, company_id) VALUES (?, ?, ?)");
                $stmt->execute([$email, $reset_token, $company_id]);

                // メール送信
                $reset_link = BASE_PATH . '/reset_password.php?token=' . $reset_token;
                $subject = "【シフト管理システム】パスワード再設定のお知らせ";
                $body = "以下のリンクをクリックしてパスワードを再設定してください。\n\n" . $reset_link . "\n\n※このリンクは24時間有効です。";
                
                if (send_mail($email, $subject, $body)) {
                    $success = 'パスワード再設定用のリンクをメールで送信しました。';
                    $companies = []; // リセット
                } else {
                    $error = 'メールの送信に失敗しました。';
                }
            } else {
                $error = 'ユーザーが見つかりませんでした。';
            }

        } else {
            // 会社指定なし：ユーザー検索
            $stmt = $pdo->prepare("
                SELECT u.company_id, u.username, c.company_name 
                FROM users u 
                JOIN companies c ON u.company_id = c.company_id 
                WHERE u.email = ? AND u.is_deleted = 0
            ");
            $stmt->execute([$email]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($results) === 0) {
                // セキュリティのため、存在しなくても送信したように見せるのが一般的だが、
                // 今回は既存実装に合わせてエラーメッセージを出すか、あるいは「送信しました」とかにするか。
                // 既存実装はエラーを出していたのでエラーを出す。
                $error = '登録されていないメールアドレスです。';
            } elseif (count($results) === 1) {
                // 1社のみ：自動的に company_id を設定してリセット処理へ（再帰的にPOSTするか、ここで処理するか）
                // ここで処理する方がユーザーの手間が少ない
                $company_id = $results[0]['company_id'];
                
                // --- リセット処理重複 ---
                $reset_token = bin2hex(random_bytes(32));
                $stmt = $pdo->prepare("INSERT INTO password_resets (email, token, company_id) VALUES (?, ?, ?)");
                $stmt->execute([$email, $reset_token, $company_id]);

                // メール送信
                $reset_link = BASE_PATH . '/reset_password.php?token=' . $reset_token;
                $subject = "【シフト管理システム】パスワード再設定のお知らせ";
                $body = "以下のリンクをクリックしてパスワードを再設定してください。\n\n" . $reset_link . "\n\n※このリンクは24時間有効です。";
                
                if (send_mail($email, $subject, $body)) {
                    $success = 'パスワード再設定用のリンクをメールで送信しました。';
                } else {
                    $error = 'メールの送信に失敗しました。';
                }
                // -----------------------

            } else {
                // 複数社：会社リストを表示して選択させる
                $companies = $results;
            }
        }
    }
}

require_once __DIR__ . '/views/forgot_password_view.php';

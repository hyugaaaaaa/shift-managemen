<?php
require_once __DIR__ . '/config.php';
session_start();
require_once __DIR__ . '/template.php';

// 既にログインしている場合はダッシュボードへ
if (isset($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/dashboard.php');
    exit;
}

$pdo = getPDO();
$error = '';

$company_name = '';
$representative_name = '';
$phone_number = '';
$address = '';
$username = '';
$email = '';
$agree_terms = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力値取得
    $company_name = trim($_POST['company_name'] ?? '');
    $representative_name = trim($_POST['representative_name'] ?? '');
    $phone_number = trim($_POST['phone_number'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    // バリデーション
    if (!validate_csrf_token($csrf_token)) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif (empty($company_name) || empty($username) || empty($email) || empty($password)) {
        $error = '必須項目を入力してください。';
    } elseif (!$agree_terms) {
        $error = '利用規約とプライバシーポリシーへの同意が必要です。';
    } elseif ($password !== $confirm_password) {
        $error = 'パスワードが一致しません。';
    } elseif (strlen($password) < 8) {
        $error = 'パスワードは8文字以上で設定してください。';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Generate Company Code
            // 重複チェックループ（念のため）
            $company_code = '';
            for ($i = 0; $i < 5; $i++) {
                $temp_code = generate_company_code();
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM companies WHERE company_code = ?");
                $stmt->execute([$temp_code]);
                if ($stmt->fetchColumn() == 0) {
                    $company_code = $temp_code;
                    break;
                }
            }
            if (empty($company_code)) {
                throw new Exception("システムエラー：企業コードの生成に失敗しました。もう一度お試しください。");
            }

            // 2. Insert Company
            $stmt = $pdo->prepare("INSERT INTO companies (company_name, company_code, representative_name, address, phone_number) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$company_name, $company_code, $representative_name, $address, $phone_number]);
            $company_id = $pdo->lastInsertId();

            // 3. Insert User (Owner)
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Check email/username uniqueness (本来はDB制約で弾くが、事前チェックで見栄え良くする)
            // メールは (email, company_id) でUniqueだが、新規企業なのでこの会社での重複はないはず。
            // しかし、usernameはシステム全体でUniqueのままにしているか？
            // schema.sqlを見る限り `username VARCHAR(100) NOT NULL UNIQUE` となっているため、システム全体でUnique。
            // ユーザー名を使い回したい要望が出るかもしれないが、ログインIDとして使うならユニーク必須。
            // 今回の計画で変更していないので、usernameは全体Uniqueのまま。
            
            // Removed global username uniqueness check.
            // Username only needs to be unique within the company.
            // Since this is a new company, any username is valid.

            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_type, company_id, is_agreed_terms, agreed_terms_at) VALUES (?, ?, ?, 'owner', ?, 1, NOW())");
            $stmt->execute([$username, $email, $password_hash, $company_id]);
            $user_id = $pdo->lastInsertId();

            // 4. Create Default System Settings for this company
            // 締め日・給料日のデフォルト設定
            $stmt = $pdo->prepare("INSERT INTO system_settings (company_id, setting_key, setting_value) VALUES (?, 'closing_day', '15'), (?, 'payment_day', '25')");
            $stmt->execute([$company_id, $company_id]);

            $pdo->commit();

            // 自動ログイン
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;
            $_SESSION['user_type'] = 'owner';
            $_SESSION['company_id'] = $company_id;

            // 完了画面へ変数を渡して表示（リダイレクトせず、直接includeするか、セッションに入れてリダイレクトか。
            // 完了画面のURLを作ったほうが綺麗なので、Viewを呼び出す。）
            require_once __DIR__ . '/views/register_company_complete.php';
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            if ($e->getCode() == 23000) { // Integrity constraint violation
                // user username unique, email+company unique, company code unique...
                if (strpos($e->getMessage(), 'username') !== false) {
                    $error = 'そのユーザー名は既に使用されています。';
                } else {
                    $error = '登録中にエラーが発生しました。(データベースエラー)';
                }
            } else {
                $error = $e->getMessage();
            }
        }
    }
}

require_once __DIR__ . '/views/register_company_view.php';

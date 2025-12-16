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

$company_code = '';
$username = '';
$email = '';
$agree_terms = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 入力値取得
    $company_code = strtoupper(trim($_POST['company_code'] ?? ''));
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $agree_terms = isset($_POST['agree_terms']);
    $csrf_token = $_POST['csrf_token'] ?? '';

    // バリデーション
    if (!validate_csrf_token($csrf_token)) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif (empty($company_code) || empty($username) || empty($email) || empty($password)) {
        $error = '必須項目を入力してください。';
    } elseif (!$agree_terms) {
        $error = '利用規約とプライバシーポリシーへの同意が必要です。';
    } elseif ($password !== $confirm_password) {
        $error = 'パスワードが一致しません。';
    } elseif (strlen($password) < 8) {
        $error = 'パスワードは8文字以上で設定してください。';
    } else {
        // 招待コード検証
        $company_id = validate_company_code($pdo, $company_code);
        if (!$company_id) {
            $error = '無効な招待コードです。正しいコードを入力してください。';
        } else {
            try {
                // Check username uniqueness (global) - Removed to allow multiple users with same name
                // $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
                // ... (削除)

                // Check email uniqueness (per company)
                $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND company_id = ?");
                $stmt->execute([$email, $company_id]);
                if ($stmt->fetchColumn() > 0) {
                    throw new Exception("そのメールアドレスはこの企業で既に登録されています。");
                }

                // Insert User
                // Insert User with Transaction and Locking
                $pdo->beginTransaction();
                
                try {
                    // Lock the company row to serialize ID generation for this company
                    $stmtLock = $pdo->prepare("SELECT company_id FROM companies WHERE company_id = ? FOR UPDATE");
                    $stmtLock->execute([$company_id]);
                    
                    // Calculate next company_user_id
                    $stmtMax = $pdo->prepare("SELECT MAX(company_user_id) FROM users WHERE company_id = ?");
                    $stmtMax->execute([$company_id]);
                    $maxId = $stmtMax->fetchColumn();
                    $nextCompanyUserId = ($maxId) ? $maxId + 1 : 1;

                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, user_type, company_id, company_user_id, is_agreed_terms, agreed_terms_at) VALUES (?, ?, ?, 'part-time', ?, ?, 1, NOW())");
                    $stmt->execute([$username, $email, $password_hash, $company_id, $nextCompanyUserId]);
                    $user_id = $pdo->lastInsertId();

                    $pdo->commit();

                    // 自動ログイン
                    $_SESSION['user_id'] = $user_id;
                    $_SESSION['username'] = $username;
                    $_SESSION['user_type'] = 'part-time';
                    $_SESSION['company_id'] = $company_id;

                    header('Location: ' . BASE_PATH . '/dashboard.php');
                    exit;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    throw $e;
                }

            } catch (Exception $e) {
                if ($e->getCode() == 23000) {
                    if (strpos($e->getMessage(), 'unique_email_company') !== false) {
                        $error = 'そのメールアドレスはこの企業で既に登録されています。';
                    } elseif (strpos($e->getMessage(), 'username') !== false) {
                        $error = 'そのユーザー名は既に使用されています。';
                    } else {
                        $error = '登録中にエラーが発生しました。';
                    }
                } else {
                    $error = $e->getMessage();
                }
            }
        }
    }
}

require_once __DIR__ . '/views/register_staff_view.php';

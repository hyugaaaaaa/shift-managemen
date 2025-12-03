<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー（管理者）権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$id = $_GET['id'] ?? null;
$user = [];
$error = '';
$msg = '';

// 編集の場合、既存データを取得
if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ? AND is_deleted = 0');
    $stmt->execute([$id]);
    $user = $stmt->fetch();
    if (!$user) {
        die('User not found or deleted');
    }
}

// フォーム送信時の処理（新規登録・更新）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $hourly_rate = $_POST['hourly_rate'] ?? 0;
    $transportation_expense = $_POST['transportation_expense'] ?? 0;
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    }
    // バリデーション
    elseif (empty($username)) {
        $error = 'ユーザー名は必須です。';
    } elseif (!empty($password) && $password !== $password_confirm) {
        $error = 'パスワードが一致しません。';
    } else {
        try {
            if ($id) {
                // 更新処理
                $sql = 'UPDATE users SET username = ?, hourly_rate = ?, transportation_expense = ?';
                $params = [$username, $hourly_rate, $transportation_expense];
                
                // パスワードが入力されている場合のみ更新（空欄なら変更しない）
                if (!empty($password)) {
                    $sql .= ', password_hash = ?';
                    $params[] = password_hash($password, PASSWORD_DEFAULT);
                }
                
                $sql .= ' WHERE user_id = ?';
                $params[] = $id;
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $msg = '更新しました。';
                
                // 更新後のデータを再取得
                $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
                $stmt->execute([$id]);
                $user = $stmt->fetch();
                
            } else {
                // 新規登録処理
                if (empty($password)) {
                    $error = '新規作成時はパスワードが必須です。';
                } else {
                    // パスワードをハッシュ化して保存
                    $stmt = $pdo->prepare('INSERT INTO users (username, password_hash, user_type, hourly_rate, transportation_expense) VALUES (?, ?, ?, ?, ?)');
                    $stmt->execute([$username, password_hash($password, PASSWORD_DEFAULT), 'part-time', $hourly_rate, $transportation_expense]);
                    $msg = '作成しました。';
                    $id = $pdo->lastInsertId();
                    // 登録後のデータを再取得
                    $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
                    $stmt->execute([$id]);
                    $user = $stmt->fetch();
                }
            }
        } catch (Exception $e) {
            $error = 'エラーが発生しました: ' . $e->getMessage();
        }
    }
}

// ビューの読み込み
require_once __DIR__ . '/../views/owner/user_edit_view.php';

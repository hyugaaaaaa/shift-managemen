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
    // email取得
    $email = $_POST['email'] ?? '';

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
        // 重複チェック (同社内での重複を禁止。会社が違えば同じusernameでも良いが、ログイン仕様上ユニークが必要な場合もある。
        // -> 要件変更により、usernameの重複を許可する（パスワードで識別）。
        // したがって、usernameの重複チェックは削除または警告のみにする。
        // ここでは削除する。
        
        // $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        // ...
        $error = ''; // 初期化
        
        // Email重複チェック (company_id単位)
        if (empty($error) && !empty($email)) {
            $company_id = get_current_company_id(); // この関数はconfig.phpなどで定義されていると仮定
            $sql = "SELECT COUNT(*) FROM users WHERE email = ? AND company_id = ?";
            $params = [$email, $company_id];
            
            if ($id) { // $id は編集中のユーザーID
                 $sql .= " AND user_id != ?";
                 $params[] = $id;
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            if ($stmt->fetchColumn() > 0) {
                $error = 'そのメールアドレスは既にこの会社内で使用されています。';
            }
        }

        if (empty($error)) { // 重複チェックでエラーがなければ処理を続行
            try {
                if ($id) {
                    // 更新処理
                    $sql = 'UPDATE users SET username = ?, email = ?, hourly_rate = ?, transportation_expense = ?';
                    $params = [$username, $email, $hourly_rate, $transportation_expense];
                    
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
                        // Calculate next company_user_id
                        // company_id is needed. Owner creates user for their own company.
                        $company_id = get_current_company_id();
                        
                        $stmtMax = $pdo->prepare("SELECT MAX(company_user_id) FROM users WHERE company_id = ?");
                        $stmtMax->execute([$company_id]);
                        $maxId = $stmtMax->fetchColumn();
                        $nextCompanyUserId = ($maxId) ? $maxId + 1 : 1;

                        // パスワードをハッシュ化して保存
                        $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash, user_type, hourly_rate, transportation_expense, company_id, company_user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
                        $stmt->execute([$username, $email, password_hash($password, PASSWORD_DEFAULT), 'part-time', $hourly_rate, $transportation_expense, $company_id, $nextCompanyUserId]);
                        $msg = '作成しました。';
                        $id = $pdo->lastInsertId();
                        // 登録後のデータを再取得
                        $stmt = $pdo->prepare('SELECT * FROM users WHERE user_id = ?');
                        $stmt->execute([$id]);
                        $user = $stmt->fetch();
                    }
                } catch (Exception $e) {
                $error = 'エラーが発生しました: ' . $e->getMessage();
            }
        }
    } catch (Exception $e) {
        $error = 'エラーが発生しました: ' . $e->getMessage();
    }
}
}

// ビューの読み込み
require_once __DIR__ . '/../views/owner/user_edit_view.php';

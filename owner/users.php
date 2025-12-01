<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー（管理者）権限チェック
// セッションがない、またはユーザータイプが 'owner' でない場合はログイン画面へリダイレクト
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();

// ユーザー削除処理
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $delete_user_id = $_POST['user_id'] ?? null;
    
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif ($delete_user_id) {
        try {
            $pdo->beginTransaction();
            
            // 関連データの削除 (外部キー制約でON DELETE CASCADEになっているものは自動削除されるが、念のため明示的に削除も可)
            // ここでは主要なデータを明示的に削除
            $pdo->prepare("DELETE FROM shifts_scheduled WHERE user_id = ?")->execute([$delete_user_id]);
            $pdo->prepare("DELETE FROM shifts_requested WHERE user_id = ?")->execute([$delete_user_id]);
            
            // ユーザー削除
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ? AND user_type = 'part-time'");
            $stmt->execute([$delete_user_id]);
            
            if ($stmt->rowCount() > 0) {
                $pdo->commit();
                $msg = 'ユーザーを削除しました。';
            } else {
                $pdo->rollBack();
                $error = 'ユーザーの削除に失敗しました（対象が見つからないか、権限がありません）。';
            }
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = 'エラーが発生しました: ' . $e->getMessage();
        }
    }
}

// パートタイム従業員の一覧を取得
// ユーザーID、名前、時給、交通費を取得し、名前順にソート
$stmt = $pdo->prepare('SELECT user_id, username, hourly_rate, transportation_expense FROM users WHERE user_type = ? ORDER BY username');
$stmt->execute(['part-time']);
$users = $stmt->fetchAll();

// ビューの読み込み
require_once __DIR__ . '/../views/owner/users_view.php';

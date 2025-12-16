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

// 処理: ユーザー削除（論理削除）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $delete_user_id = $_POST['user_id'] ?? null;
    $company_id = get_current_company_id();
    
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif ($delete_user_id) {
        try {
            $pdo->beginTransaction();
            
            // 論理削除 (自社のユーザーのみ)
            $stmt = $pdo->prepare("UPDATE users SET is_deleted = 1 WHERE user_id = ? AND company_id = ?");
            $stmt->execute([$delete_user_id, $company_id]);
            
            if ($stmt->rowCount() > 0) {
                // シフト希望・確定シフトも削除または無効化すべきだが、
                // 仕様上はユーザーが見えなくなればOKとする場合が多い。
                // ここでは整合性のため、確定シフトも削除しておく（将来の集計への影響を考慮）
                $stmtSch = $pdo->prepare("DELETE FROM shifts_scheduled WHERE user_id = ?");
                $stmtSch->execute([$delete_user_id]);
                
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
// 論理削除されていないユーザー、かつ自社のユーザーのみ取得
$company_id = get_current_company_id();
$stmt = $pdo->prepare('SELECT user_id, company_user_id, username, hourly_rate, transportation_expense FROM users WHERE user_type = ? AND is_deleted = 0 AND company_id = ? ORDER BY company_user_id ASC');
$stmt->execute(['part-time', $company_id]);
$users = $stmt->fetchAll();

// ビューの読み込み
require_once __DIR__ . '/../views/owner/users_view.php';

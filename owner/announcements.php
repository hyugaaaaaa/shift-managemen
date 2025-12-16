<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';
require_once __DIR__ . '/../functions.php';

// オーナー権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$error = '';
$msg = '';

// 処理: 新規作成 / 削除
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        $action = $_POST['action'] ?? '';

        if ($action === 'create') {
            $title = trim($_POST['title'] ?? '');
            $content = trim($_POST['content'] ?? '');

            if (empty($title) || empty($content)) {
                $error = 'タイトルと本文は必須です。';
            } else {
                try {
                    // DB保存
                    $company_id = get_current_company_id();
                    $stmt = $pdo->prepare("INSERT INTO announcements (company_id, title, content) VALUES (?, ?, ?)");
                    $stmt->execute([$company_id, $title, $content]);
                    $msg = 'お知らせを作成しました。';

                    // メール送信処理
                    $target = $_POST['target'] ?? 'all';
                    $target_month = $_POST['target_month'] ?? ''; // YYYY-MM
                    
                    $company_id = get_current_company_id();
                    $sql = "SELECT email, username, user_id FROM users WHERE user_type = 'part-time' AND is_deleted = 0 AND email IS NOT NULL AND email != '' AND company_id = ?";
                    $params = [$company_id];

                    if ($target === 'unsubmitted' && !empty($target_month)) {
                        // 指定月(初日〜末日)にシフト希望(shifts_requested)を出していないユーザー
                        $start_date = $target_month . '-01';
                        $end_date = date('Y-m-t', strtotime($start_date));
                        
                        $sql .= " AND user_id NOT IN (
                            SELECT DISTINCT user_id FROM shifts_requested 
                            WHERE shift_date BETWEEN ? AND ?
                        )";
                        $params[] = $start_date;
                        $params[] = $end_date;
                    }

                    $stmtUsers = $pdo->prepare($sql);
                    $stmtUsers->execute($params);
                    $users = $stmtUsers->fetchAll();

                    $subject = "【お知らせ】" . $title;
                    $body = $content . "\n\n--\n" . FROM_NAME;

                    $count = 0;
                    foreach ($users as $u) {
                        if (send_mail($u['email'], $subject, "{$u['username']} さん\n\n" . $body)) {
                            $count++;
                        }
                    }
                    if ($count > 0) {
                        $msg .= " ({$count}名へメール通知しました)";
                        
                        // 非同期でメール送信プロセスを起動
                        // リファクタリング: 共通関数を使用
                        $scriptPath = __DIR__ . '/../process_mail.php';
                        launch_background_process($scriptPath);
                    } else {
                        $msg .= " (メール送信対象がいませんでした)";
                    }

                } catch (Exception $e) {
                    $error = 'エラーが発生しました: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $delete_ids = [];
            
            // 一括削除（チェックボックス）
            if (!empty($_POST['delete_ids']) && is_array($_POST['delete_ids'])) {
                $delete_ids = $_POST['delete_ids'];
            }
            // 個別削除（ボタン）
            elseif (!empty($_POST['delete_id'])) {
                $delete_ids[] = $_POST['delete_id'];
            }

            if (!empty($delete_ids)) {
                try {
                    // プレースホルダー作成
                    $in  = str_repeat('?,', count($delete_ids) - 1) . '?';
                    $company_id = get_current_company_id();
                    // 他社のお知らせを消さないように company_id チェック追加
                    // DELETE FROM ... WHERE id IN (...) AND company_id = ?
                    // IN句の後にパラメータを追加する必要がある。
                    
                    $sql = "DELETE FROM announcements WHERE id IN ($in) AND company_id = ?";
                    $params = $delete_ids;
                    $params[] = $company_id;
                    
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $msg = 'お知らせを削除しました。';
                } catch (Exception $e) {
                    $error = '削除に失敗しました: ' . $e->getMessage();
                }
            } else {
                $error = '削除対象が選択されていません。';
            }
        }
    }
}

//のお知らせ一覧取得 (自社のみ)
$company_id = get_current_company_id();
$stmt = $pdo->prepare("SELECT * FROM announcements WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$company_id]);
$announcements = $stmt->fetchAll();

render_header('お知らせ管理');
require_once __DIR__ . '/../views/owner/announcements_view.php';
render_footer();

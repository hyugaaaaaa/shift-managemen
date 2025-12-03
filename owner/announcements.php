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
                    $stmt = $pdo->prepare("INSERT INTO announcements (title, content) VALUES (?, ?)");
                    $stmt->execute([$title, $content]);
                    $msg = 'お知らせを作成しました。';

                    // LINE通知送信
                    // ID設定済みの全ユーザーを取得
                    $stmt_users = $pdo->query("SELECT line_user_id FROM users WHERE line_user_id IS NOT NULL AND is_deleted = 0");
                    $users = $stmt_users->fetchAll(PDO::FETCH_COLUMN);

                    $notify_message = "【お知らせ】\n" . $title . "\n\n" . $content;
                    $notify_count = 0;

                    foreach ($users as $uid) {
                        if (send_line_push($pdo, $uid, $notify_message)) {
                            $notify_count++;
                        }
                    }
                    
                    if ($notify_count > 0) {
                        $msg .= " ({$notify_count}人にLINE通知を送信しました)";
                    }

                } catch (Exception $e) {
                    $error = 'エラーが発生しました: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id = $_POST['id'] ?? 0;
            if ($id) {
                try {
                    $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
                    $stmt->execute([$id]);
                    $msg = 'お知らせを削除しました。';
                } catch (Exception $e) {
                    $error = '削除に失敗しました: ' . $e->getMessage();
                }
            }
        }
    }
}

// お知らせ一覧取得
$stmt = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC");
$announcements = $stmt->fetchAll();

render_header('お知らせ管理');
require_once __DIR__ . '/../views/owner/announcements_view.php';
render_footer();

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

<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$msg = '';
$error = '';

// 処理: 追加 / 削除
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        if ($action === 'add') {
            $name = trim($_POST['template_name'] ?? '');
            $start = trim($_POST['start_time'] ?? '');
            $end = trim($_POST['end_time'] ?? '');
            
            if ($name === '' || $start === '' || $end === '') {
                $error = 'すべての項目を入力してください。';
            } else {
                try {
                    $company_id = get_current_company_id();
                    $stmt = $pdo->prepare("INSERT INTO shift_templates (company_id, template_name, start_time, end_time) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$company_id, $name, $start, $end]);
                    $msg = 'テンプレートを追加しました。';
                } catch (Exception $e) {
                    $error = '追加に失敗しました: ' . $e->getMessage();
                }
            }
        } elseif ($action === 'delete') {
            $id = intval($_POST['template_id'] ?? 0);
            if ($id > 0) {
                try {
                    $company_id = get_current_company_id();
                    // 自社のテンプレートのみ削除可能
                    $stmt = $pdo->prepare("DELETE FROM shift_templates WHERE template_id = ? AND company_id = ?");
                    $stmt->execute([$id, $company_id]);
                    $msg = 'テンプレートを削除しました。';
                } catch (Exception $e) {
                    $error = '削除に失敗しました: ' . $e->getMessage();
                }
            }
        }
    }
}

// テンプレート// 一覧取得 (自社のみ)
$company_id = get_current_company_id();
$stmt = $pdo->prepare("SELECT * FROM shift_templates WHERE company_id = ? ORDER BY created_at DESC");
$stmt->execute([$company_id]);
$templates = $stmt->fetchAll();

require_once __DIR__ . '/../views/owner/shift_templates_view.php';

<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner' || empty($_SESSION['company_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
         // エラーメッセージをセッションに入れて戻るなどは省略。単純に終了するか、エラー画面へ。
         die('セッションが無効です。');
    }

    try {
        $pdo = getPDO();
        
        // 新しいコード生成
        $new_code = '';
        for ($i = 0; $i < 5; $i++) {
            $temp_code = generate_company_code();
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM companies WHERE company_code = ?");
            $stmt->execute([$temp_code]);
            if ($stmt->fetchColumn() == 0) {
                $new_code = $temp_code;
                break;
            }
        }
        
        if ($new_code) {
             $stmt = $pdo->prepare("UPDATE companies SET company_code = ? WHERE company_id = ?");
             $stmt->execute([$new_code, $_SESSION['company_id']]);
             
             // 成功メッセージ等はView側で出す仕組みがあれば出すが、今回は簡易的にリダイレクトのみ
             header('Location: system_settings.php?msg=regenerated');
             exit;
        } else {
             die('コード生成に失敗しました。');
        }

    } catch (Exception $e) {
        die('システムエラーが発生しました。');
    }
} else {
    header('Location: system_settings.php');
    exit;
}

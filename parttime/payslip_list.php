<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'part-time') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$user_id = $_SESSION['user_id'];
$msg = '';

// 同意処理
// ユーザーが同意ボタンを押した場合、DBを更新
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['consent'])) {
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $msg = 'セッションが無効です。もう一度お試しください。';
    } else {
        $stmt = $pdo->prepare("UPDATE users SET payslip_consent = 1, payslip_consent_date = NOW() WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $msg = '電子交付に同意しました。';
    }
}

// ユーザー情報取得（同意状態の確認用）
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// 過去12ヶ月分のリスト生成
// 現在から12ヶ月前までの年月リストを作成
$months = [];
for ($i = 0; $i < 12; $i++) {
    $months[] = date('Y-m', strtotime("-$i month"));
}

// ビューの読み込み
require_once __DIR__ . '/../views/parttime/payslip_list_view.php';

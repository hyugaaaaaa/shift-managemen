<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 共通ヘッダー出力関数
function render_header($title = 'シフト管理'){
    ?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title); ?></title>
  <!-- Google Fonts: Inter (英数字) と Noto Sans JP (日本語) を読み込み -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
  <!-- カスタムCSS (キャッシュバスター付き) -->
  <link href="<?php echo BASE_PATH; ?>/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>
<!-- ナビゲーションバー -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo !empty($_SESSION['user_id']) ? BASE_PATH . '/dashboard.php' : BASE_PATH . '/index.php'; ?>">シフト管理</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if(!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/dashboard.php">ダッシュボード</a></li>
          <?php if($_SESSION['user_type'] === 'owner'): ?>
            <!-- オーナー用メニュー -->
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php">希望一覧</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/users.php">従業員管理</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/monthly_hours.php">月間時間</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/attendance_approval.php">勤怠承認</a></li>
          <?php else: ?>
            <!-- アルバイト用メニュー -->
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/submit_shift.php">希望提出</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/view_schedule.php">確定シフト</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/attendance_list.php">勤怠確認・修正</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/payslip_list.php">給与明細</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if(!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><span class="nav-link"><?php echo htmlspecialchars($_SESSION['username']); ?></span></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/help.php">ヘルプ</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/logout.php">ログアウト</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/index.php">ログイン</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<div class="container">
<?php
}

// 共通フッター出力関数
function render_footer(){
    ?>
</div>
<footer class="text-center py-4">
  <small class="text-muted">&copy; シフト管理</small>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
}

?>

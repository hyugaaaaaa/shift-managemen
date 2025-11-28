<?php
session_start();

function render_header($title = 'シフト管理'){
    ?>
<!doctype html>
<html lang="ja">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?php echo htmlspecialchars($title); ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="<?php echo BASE_PATH; ?>/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo !empty($_SESSION['user_id']) ? BASE_PATH . '/dashboard.php' : BASE_PATH . '/index.php'; ?>">シフト管理</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if(!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/dashboard.php">ダッシュボード</a></li>
          <?php if($_SESSION['user_type'] === 'owner'): ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php">希望一覧</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/manage_wages.php">時給管理</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/monthly_hours.php">月間時間</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/submit_shift.php">希望提出</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/view_schedule.php">確定シフト</a></li>
          <?php endif; ?>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav">
        <?php if(!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><span class="nav-link"><?php echo htmlspecialchars($_SESSION['username']); ?></span></li>
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

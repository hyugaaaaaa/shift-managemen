<?php
if (session_status() === PHP_SESSION_NONE) {
    // セッションの有効期限を30分に設定（ガベージコレクション用）
    ini_set('session.gc_maxlifetime', 1800);
    session_start();
}

// セッションタイムアウト機能 (30分 = 1800秒)
if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
    // 最終アクティビティから30分以上経過していたらセッション破棄
    session_unset();
    session_destroy();
    header('Location: ' . BASE_PATH . '/index.php?timeout=1');
    exit;
}
// 最終アクティビティ時刻を更新
$_SESSION['last_activity'] = time();

// 共通ヘッダー出力関数
function render_header($title = 'シフト管理', $show_nav = true, $extra_css = []){
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
  <link href="<?php echo BASE_PATH; ?>/css/common.css?v=<?php echo time(); ?>" rel="stylesheet">
  <?php
  if (!empty($extra_css)) {
      foreach ($extra_css as $css_file) {
          echo '<link href="' . BASE_PATH . '/css/' . $css_file . '?v=' . time() . '" rel="stylesheet">' . "\n";
      }
  }
  ?>
</head>
<body class="<?php echo !$show_nav ? 'login-page' : ''; ?>">
<?php if($show_nav): ?>
<!-- ナビゲーションバー -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4 shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?php echo !empty($_SESSION['user_id']) ? BASE_PATH . '/dashboard.php' : BASE_PATH . '/index.php'; ?>">
        <i class="bi bi-calendar-check me-1"></i>シフト管理
    </a>
    
    <!-- Mobile User Name Only (Replaces Toggler) -->
    <?php if(!empty($_SESSION['username'])): ?>
    <div class="d-lg-none text-white fw-bold">
        <i class="bi bi-person-circle me-1"></i>
        <?php echo htmlspecialchars($_SESSION['username']); ?>
    </div>
    <?php endif; ?>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if(!empty($_SESSION['user_id'])): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/dashboard.php">ダッシュボード</a></li>
          <?php if($_SESSION['user_type'] === 'owner'): ?>
            <!-- オーナー用メニュー -->
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php">シフト承認</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/users.php">従業員管理</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/monthly_hours.php">給与集計</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/owner/export_data.php">データ出力</a></li>
            <li class="nav-item dropdown">
              <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                システム管理
              </a>
              <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/holidays.php">定休日設定</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/announcements.php">お知らせ管理</a></li>
                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/shift_templates.php">シフトパターン設定</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/system_settings.php">システム設定</a></li>
              </ul>
            </li>
          <?php else: ?>
            <!-- アルバイト用メニュー -->
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/submit_shift.php">シフト希望提出</a></li>
            <li class="nav-item"><a class="nav-link" href="<?php echo BASE_PATH; ?>/parttime/view_schedule.php">確定シフト</a></li>
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
<?php else: ?>
<!-- ナビなし時のコンテナ -->
<div class="container-fluid p-0 min-vh-100 d-flex flex-column align-items-center justify-content-center">
<?php endif; ?>
<?php
}

// 共通フッター出力関数
function render_footer(){
    ?>
</div>
<!-- Toast Container -->
<div class="toast-container position-fixed top-0 end-0 p-3"></div>

<footer class="text-center py-4 text-muted small">
  <div class="container">
    &copy; Shift Management System
  </div>
</footer>

<!-- Mobile Bottom Navigation -->
<?php if(isset($_SESSION['user_id'])): ?>
<nav class="bottom-nav d-flex d-md-none">
    <?php 
    $current_page = basename($_SERVER['PHP_SELF']);
    // Determine user type for links
    $user_type = $_SESSION['user_type'] ?? 'parttime';
    ?>

    <!-- Home -->
    <a href="<?php echo BASE_PATH; ?>/dashboard.php" class="bottom-nav-item <?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
        <i class="bi bi-house-door<?php echo $current_page == 'dashboard.php' ? '-fill' : ''; ?>"></i>
        <span>ホーム</span>
    </a>

    <?php if($user_type === 'owner'): ?>
        <!-- Owner: Approve -->
        <a href="<?php echo BASE_PATH; ?>/owner/manage_requests.php" class="bottom-nav-item <?php echo $current_page == 'manage_requests.php' ? 'active' : ''; ?>">
            <i class="bi bi-check-square<?php echo $current_page == 'manage_requests.php' ? '-fill' : ''; ?>"></i>
            <span>承認</span>
        </a>
        <!-- Owner: Users -->
        <a href="<?php echo BASE_PATH; ?>/owner/users.php" class="bottom-nav-item <?php echo $current_page == 'users.php' ? 'active' : ''; ?>">
            <i class="bi bi-people<?php echo $current_page == 'users.php' ? '-fill' : ''; ?>"></i>
            <span>従業員</span>
        </a>
        <!-- Owner: Settings (Menu) -->
        <a href="<?php echo BASE_PATH; ?>/owner/system_settings.php" class="bottom-nav-item <?php echo $current_page == 'system_settings.php' ? 'active' : ''; ?>">
            <i class="bi bi-gear<?php echo $current_page == 'system_settings.php' ? '-fill' : ''; ?>"></i>
            <span>設定</span>
        </a>
    <?php else: ?>
        <!-- Parttime: Submit Shift -->
        <a href="<?php echo BASE_PATH; ?>/parttime/submit_shift.php" class="bottom-nav-item <?php echo $current_page == 'submit_shift.php' ? 'active' : ''; ?>">
            <i class="bi bi-calendar-plus<?php echo $current_page == 'submit_shift.php' ? '-fill' : ''; ?>"></i>
            <span>提出</span>
        </a>
        <!-- Parttime: View Schedule -->
        <a href="<?php echo BASE_PATH; ?>/parttime/view_schedule.php" class="bottom-nav-item <?php echo $current_page == 'view_schedule.php' ? 'active' : ''; ?>">
            <i class="bi bi-calendar-check<?php echo $current_page == 'view_schedule.php' ? '-fill' : ''; ?>"></i>
            <span>確認</span>
        </a>
    <?php endif; ?>

    <!-- Help -->
    <a href="<?php echo BASE_PATH; ?>/help.php" class="bottom-nav-item <?php echo $current_page == 'help.php' ? 'active' : ''; ?>">
        <i class="bi bi-question-circle<?php echo $current_page == 'help.php' ? '-fill' : ''; ?>"></i>
        <span>ヘルプ</span>
    </a>


</nav>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?php echo BASE_PATH; ?>/js/main.js?v=<?php echo time(); ?>"></script>
</body>
</html>
<?php
}

?>

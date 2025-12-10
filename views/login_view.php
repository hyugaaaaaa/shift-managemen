<?php render_header('ログイン', false); ?>

<div class="login-card fade-in-up">
  <div class="login-header">
    <div class="brand-icon-wrapper">
      <i class="bi bi-calendar-check brand-icon"></i>
    </div>
    <h1 class="login-title">Shift Manager</h1>
    <p class="mb-0 small text-white-50">シフト管理システムへようこそ</p>
  </div>
  
  <div class="login-body">
    <?php if(!empty($_GET['logged_out'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>ログアウトしました。
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if(!empty($_GET['registered'])): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i>登録が完了しました。
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i><?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <form method="post" autocomplete="off" onsubmit="showLoading(this)">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
      
      <div class="form-floating mb-3">
        <input type="text" name="username" class="form-control" id="floatingInput" placeholder="ユーザー名" required autocomplete="off">
        <label for="floatingInput"><i class="bi bi-person me-1"></i> ユーザー名</label>
      </div>
      
      <div class="form-floating mb-4">
        <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="パスワード" required autocomplete="new-password">
        <label for="floatingPassword"><i class="bi bi-lock me-1"></i> パスワード</label>
      </div>

      <button class="btn btn-primary btn-login" type="submit">
        <span class="spinner-border spinner-border-sm d-none me-2" role="status" aria-hidden="true"></span>
        ログイン
      </button>

      <div class="login-footer">
        <a href="forgot_password.php">パスワードをお忘れですか？</a>
      </div>
    </form>
    
    <?php if(!empty($show_register_link) && $show_register_link): ?>
    <div class="mt-4 text-center border-top pt-3">
        <p class="small text-muted mb-2">まだアカウントをお持ちでない場合</p>
        <a href="register.php" class="btn btn-outline-success btn-sm rounded-pill px-3">
            <i class="bi bi-person-plus me-1"></i>新規登録（初期設定）
        </a>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php render_footer(); ?>

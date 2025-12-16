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
        <input type="text" name="company_code" class="form-control" id="floatingCompanyCode" placeholder="企業コード" required autocomplete="organization" value="<?php echo htmlspecialchars($saved_company_code); ?>">
        <label for="floatingCompanyCode"><i class="bi bi-building me-1"></i> 企業コード</label>
      </div>

      <div class="form-floating mb-3">
        <input type="text" name="username" class="form-control" id="floatingInput" placeholder="ユーザー名" required autocomplete="username">
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
    
    <div class="mt-4 text-center border-top pt-3">
        <p class="small text-muted mb-3">まだアカウントをお持ちでない場合</p>
        
        <div class="d-grid gap-2 col-10 mx-auto">
            <a href="register_company.php" class="btn btn-outline-primary btn-sm rounded-pill">
                <i class="bi bi-building me-1"></i> 企業アカウントを作成 (オーナー)
            </a>
            <a href="register_staff.php" class="btn btn-outline-success btn-sm rounded-pill">
                <i class="bi bi-person-badge me-1"></i> アルバイトとして登録 (招待あり)
            </a>
        </div>
    </div>
  </div>
</div>

<?php render_footer(); ?>

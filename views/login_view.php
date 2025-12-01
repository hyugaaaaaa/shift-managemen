<?php render_header('ログイン'); ?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <h1 class="h3 mb-3">ログイン</h1>
    <?php if(!empty($_GET['logged_out'])): ?>
      <div class="alert alert-success">ログアウトしました。</div>
    <?php endif; ?>
    <?php if(!empty($_GET['registered'])): ?>
      <div class="alert alert-success">登録が完了しました。ログインしてください。</div>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
      <div class="mb-3">
        <label class="form-label">ユーザー名</label>
        <input name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">パスワード</label>
        <input name="password" type="password" class="form-control" required>
      </div>
        <button class="btn btn-primary" type="submit">ログイン</button>
    </form>
  </div>
</div>
<?php render_footer(); ?>

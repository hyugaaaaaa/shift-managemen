<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

// ログイン処理
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if($username === '' || $password === ''){
        $error = 'ユーザー名とパスワードを入力してください。';
    } else {
        $pdo = getPDO();
        $stmt = $pdo->prepare('SELECT user_id, username, password_hash, user_type FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        if($user && password_verify($password, $user['password_hash'])){
            // 認証成功
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_type'] = $user['user_type'];
            // ログイン直後はダッシュボード（月表示のシフト表）へ遷移
            header('Location: dashboard.php');
            exit;
        } else {
            $error = '認証に失敗しました。ユーザー名・パスワードを確認してください。';
        }
    }
}

render_header('ログイン');
?>
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
      <div class="mb-3">
        <label class="form-label">ユーザー名</label>
        <input name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">パスワード</label>
        <input name="password" type="password" class="form-control" required>
      </div>
        <button class="btn btn-primary" type="submit">ログイン</button>
        <a class="btn btn-link" href="register.php">新規登録はこちら</a>
    </form>
  </div>
</div>

<?php render_footer(); ?>

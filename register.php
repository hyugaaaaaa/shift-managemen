<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

// 登録処理
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    $user_type = ($_POST['user_type'] === 'owner') ? 'owner' : 'part-time';

    if($username === '' || $password === '' || $password_confirm === ''){
        $error = 'すべての項目を入力してください。';
    } elseif($password !== $password_confirm){
        $error = 'パスワードが一致しません。';
    } elseif(mb_strlen($username) < 3){
        $error = 'ユーザー名は3文字以上にしてください。';
    } elseif(strlen($password) < 6){
        $error = 'パスワードは6文字以上にしてください。';
    } else {
        $pdo = getPDO();
        // 既存ユーザー確認
        $stmt = $pdo->prepare('SELECT COUNT(*) AS cnt FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        if($row && $row['cnt'] > 0){
            $error = 'そのユーザー名は既に使われています。別のユーザー名を選んでください。';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $ins = $pdo->prepare('INSERT INTO users (username, password_hash, user_type) VALUES (?, ?, ?)');
            $ins->execute([$username, $hash, $user_type]);
            header('Location: index.php?registered=1');
            exit;
        }
    }
}

render_header('新規登録');
?>
<div class="row justify-content-center">
  <div class="col-md-6">
    <h1 class="h3 mb-3">新規登録</h1>
    <?php if(!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">ユーザー名</label>
        <input name="username" class="form-control" required value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>">
      </div>
      <div class="mb-3">
        <label class="form-label">パスワード</label>
        <input name="password" type="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">パスワード（確認）</label>
        <input name="password_confirm" type="password" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">ユーザー種別</label>
        <select name="user_type" class="form-select">
          <option value="part-time" <?php echo (($_POST['user_type'] ?? '') === 'part-time') ? 'selected' : '' ?>>パートタイム</option>
          <option value="owner" <?php echo (($_POST['user_type'] ?? '') === 'owner') ? 'selected' : '' ?>>管理者(オーナー)</option>
        </select>
      </div>
      <button class="btn btn-primary" type="submit">登録する</button>
      <a class="btn btn-link" href="index.php">ログイン画面へ戻る</a>
    </form>
  </div>
</div>

<?php render_footer(); ?>

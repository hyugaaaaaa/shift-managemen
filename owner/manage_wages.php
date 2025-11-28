<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../template.php';

// オーナー専用
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$error = null;
$msg = null;

// 更新処理（個別）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['action'])) {
    $action = $_POST['action'];
    try {
        if ($action === 'update' && isset($_POST['user_id']) && isset($_POST['hourly_rate'])) {
            $uid = intval($_POST['user_id']);
            $rate = str_replace(',', '', trim($_POST['hourly_rate']));
            if (!is_numeric($rate) || floatval($rate) < 0) throw new Exception('無効な時給です。');
            $stmt = $pdo->prepare('UPDATE users SET hourly_rate = ? WHERE user_id = ?');
            $stmt->execute([number_format((float)$rate,2,'.',''), $uid]);
            $msg = '更新しました。';
        } elseif ($action === 'set_default' && isset($_POST['default_rate'])) {
            $rate = str_replace(',', '', trim($_POST['default_rate']));
            if (!is_numeric($rate) || floatval($rate) < 0) throw new Exception('無効な時給です。');
            $pdo->prepare('UPDATE users SET hourly_rate = ? WHERE user_type = ?')->execute([number_format((float)$rate,2,'.',''), 'part-time']);
            $msg = '全員の時給を更新しました。';
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// ユーザー一覧取得
try {
    $stmt = $pdo->prepare('SELECT user_id, username, hourly_rate FROM users WHERE user_type = ? ORDER BY username');
    $stmt->execute(['part-time']);
    $users = $stmt->fetchAll();
} catch (Exception $e) {
    $users = [];
    $error = $e->getMessage() . ' — DB に `hourly_rate` カラムがない場合は `db/schema.sql` を更新して、データベースを再構築/ALTER TABLE を実行してください。';
}

render_header('時給管理');
?>
<div class="row">
  <div class="col-md-10">
    <h1 class="h4 mb-3">時給管理</h1>
    <?php if(!empty($msg)): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
    <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>

    <div class="mb-3">
      <form method="post" class="row g-2">
        <input type="hidden" name="action" value="set_default">
        <div class="col-auto">
          <label class="form-label">全員の時給を一括設定（¥）</label>
          <input name="default_rate" class="form-control" value="1000">
        </div>
        <div class="col-auto align-self-end">
          <button class="btn btn-warning">一括設定</button>
        </div>
      </form>
    </div>

    <table class="table table-sm table-bordered">
      <thead>
        <tr>
          <th class="user-header">ユーザー</th>
          <th class="wage-header">時給（¥）</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($users as $u): ?>
        <tr>
          <td class="user-col"><?php echo htmlspecialchars($u['username']); ?></td>
          <td class="wage-input-col">
            <form method="post" class="d-flex">
              <input type="hidden" name="action" value="update">
              <input type="hidden" name="user_id" value="<?php echo intval($u['user_id']); ?>">
              <input name="hourly_rate" class="form-control form-control-sm me-2 manage-wage-input" value="<?php echo htmlspecialchars(number_format(round($u['hourly_rate']))); ?>">
              <button class="btn btn-sm btn-primary">更新</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php render_footer(); ?>

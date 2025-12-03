<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー専用ページ
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();

// 処理: 承認 / 却下
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $request_id = intval($_POST['request_id'] ?? 0);

    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } elseif ($request_id > 0 && in_array($action, ['approve','reject'], true)) {
        try {
            $pdo->beginTransaction();
            $stmt = $pdo->prepare('SELECT user_id, shift_date, start_time, end_time FROM shifts_requested WHERE request_id = ? FOR UPDATE');
            $stmt->execute([$request_id]);
            $req = $stmt->fetch();
            if (!$req) {
                throw new Exception('該当リクエストが見つかりません。');
            }

            if ($action === 'approve') {
                // 定休日チェック
                $stmt_holiday = $pdo->prepare("SELECT COUNT(*) FROM holidays WHERE holiday_date = ?");
                $stmt_holiday->execute([$req['shift_date']]);
                if ($stmt_holiday->fetchColumn() > 0) {
                    throw new Exception('指定された日付は定休日です。承認できません。');
                }

                // shifts_scheduled に追加
                $ins = $pdo->prepare('INSERT INTO shifts_scheduled (user_id, shift_date, start_time, end_time) VALUES (?, ?, ?, ?)');
                $ins->execute([$req['user_id'], $req['shift_date'], $req['start_time'], $req['end_time']]);
                // リクエストを approved に
                $up = $pdo->prepare('UPDATE shifts_requested SET request_status = "approved" WHERE request_id = ?');
                $up->execute([$request_id]);
                $msg = '承認して確定シフトに追加しました。';
            } else {
                $up = $pdo->prepare('UPDATE shifts_requested SET request_status = "rejected" WHERE request_id = ?');
                $up->execute([$request_id]);
                $msg = '却下しました。';
            }
            $pdo->commit();
            header('Location: ' . BASE_PATH . '/owner/manage_requests.php?msg=' . urlencode($msg));
            exit;
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    } else {
        $error = '無効なリクエストです。';
    }
}

// リクエスト一覧取得（オプション: フィルタ）
$filter = $_GET['filter'] ?? 'pending'; // pending/all/approved/rejected
$sql = 'SELECT r.request_id, r.user_id, r.shift_date, r.start_time, r.end_time, r.request_status, r.submitted_at, u.username
        FROM shifts_requested r JOIN users u ON r.user_id = u.user_id';
if ($filter === 'all') {
    $sql .= ' ORDER BY r.request_status, r.shift_date';
} else {
    $sql .= ' WHERE r.request_status = ? ORDER BY r.shift_date';
}

if ($filter === 'all') {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
} else {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$filter]);
}
$requests = $stmt->fetchAll();

// ステータス表示ラベル（日本語）
function status_label($s){
  $map = [
    'pending' => '保留',
    'approved' => '承認済み',
    'rejected' => '却下',
  ];
  return $map[$s] ?? htmlspecialchars($s);
}

render_header('希望シフト一覧（オーナー）');
?>
<div class="row">
  <div class="col-md-12">
    <h1 class="h4 mb-3">希望シフト一覧</h1>
    <div class="mb-3">
      <a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=pending">保留</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=all">すべて</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=approved">承認済み</a>
      <a class="btn btn-sm btn-outline-secondary" href="<?php echo BASE_PATH; ?>/owner/manage_requests.php?filter=rejected">却下</a>
    </div>

    <?php if(!empty($_GET['msg'])): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($_GET['msg']); ?></div>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if(empty($requests)): ?>
      <div class="alert alert-info">該当する希望シフトはありません。</div>
    <?php else: ?>
      <table class="table table-sm">
        <thead>
          <tr>
            <th>ユーザー</th>
            <th>日付</th>
            <th>開始</th>
            <th>終了</th>
            <th>状態</th>
            <th>提出日時</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($requests as $r): ?>
          <tr>
            <td><?php echo htmlspecialchars($r['username']); ?></td>
            <td><?php echo htmlspecialchars($r['shift_date']); ?></td>
            <td><?php echo htmlspecialchars(substr($r['start_time'],0,5)); ?></td>
            <td><?php echo htmlspecialchars(substr($r['end_time'],0,5)); ?><?php if(strtotime($r['end_time']) <= strtotime($r['start_time'])) echo ' <span class="text-muted">(翌日)</span>'; ?></td>
            <td><?php echo htmlspecialchars(status_label($r['request_status'])); ?></td>
            <td><?php echo htmlspecialchars($r['submitted_at']); ?></td>
            <td>
              <?php if($r['request_status'] === 'pending'): ?>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                  <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                  <button name="action" value="approve" class="btn btn-sm btn-success" onclick="return confirm('この希望を承認して確定シフトに追加しますか？');">承認</button>
                </form>
                <form method="post" style="display:inline">
                  <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                  <input type="hidden" name="request_id" value="<?php echo intval($r['request_id']); ?>">
                  <button name="action" value="reject" class="btn btn-sm btn-danger" onclick="return confirm('この希望を却下しますか？');">却下</button>
                </form>
              <?php else: ?>
                -
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<?php render_footer(); ?>

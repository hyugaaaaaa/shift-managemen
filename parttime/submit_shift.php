<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../template.php';

if(empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'part-time'){
  header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
// Prepare default values
$shift_date = $_POST['shift_date'] ?? date('Y-m-d');
$start_time = $_POST['start_time'] ?? '';
$end_time = $_POST['end_time'] ?? '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $shift_date = $_POST['shift_date'] ?? '';
  $start_time = $_POST['start_time'] ?? '';
  $end_time = $_POST['end_time'] ?? '';
  if(!$shift_date || !$start_time || !$end_time){
    $error = '日付と開始・終了時刻を入力してください。';
  } else {
    $stmt = $pdo->prepare('INSERT INTO shifts_requested (user_id, shift_date, start_time, end_time, request_status) VALUES (?, ?, ?, ?, ? )');
    $stmt->execute([$_SESSION['user_id'], $shift_date, $start_time, $end_time, 'pending']);
    $success = '希望シフトを登録しました。';
    // clear inputs after success
    $shift_date = date('Y-m-d');
    $start_time = '';
    $end_time = '';
  }
}

render_header('希望シフト提出');
?>
<div class="row">
  <div class="col-md-8">
    <h2>希望シフト提出</h2>
    <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if(!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <form method="post">
      <div class="mb-3">
        <label class="form-label">日付</label>
        <input type="date" name="shift_date" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">開始時刻</label>
        <input type="time" name="start_time" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">終了時刻</label>
        <input type="time" name="end_time" class="form-control" required>
      </div>
      <button class="btn btn-primary" type="submit">提出</button>
    </form>
  </div>
</div>

<?php render_footer(); ?>

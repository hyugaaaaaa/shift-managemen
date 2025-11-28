<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../template.php';

if(empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner'){
    header('Location: /index.php');
    exit;
}

$pdo = getPDO();
$selected_month = $_GET['month'] ?? date('Y-m');

// 月の開始日と終了日を計算
$first_day = $selected_month . '-01';
$last_day = date('Y-m-t', strtotime($first_day));

 $stmt = $pdo->prepare(
  'SELECT u.user_id,
      u.username,
      u.hourly_rate,
      COALESCE(SUM(
        CASE
          WHEN s.shift_date IS NULL THEN 0
          WHEN s.end_time <= s.start_time THEN
            TIMESTAMPDIFF(MINUTE,
              CONCAT(s.shift_date, " ", s.start_time),
              DATE_ADD(CONCAT(s.shift_date, " ", s.end_time), INTERVAL 1 DAY)
            )
          ELSE
            TIMESTAMPDIFF(MINUTE,
              CONCAT(s.shift_date, " ", s.start_time),
              CONCAT(s.shift_date, " ", s.end_time)
            )
        END
      ),0) AS minutes
   FROM users u
   LEFT JOIN shifts_scheduled s ON s.user_id = u.user_id AND s.shift_date BETWEEN ? AND ?
   WHERE u.user_type = ?
   GROUP BY u.user_id, u.username
   ORDER BY u.username'
);
$stmt->execute([$first_day, $last_day, 'part-time']);
$rows = $stmt->fetchAll();

render_header('月間勤務時間');
?>
<div class="row">
  <div class="col-md-10">
    <h2>月間勤務時間</h2>
    <form class="row g-2 mb-3" method="get">
      <div class="col-auto">
        <label class="form-label">対象月</label>
        <input type="month" name="month" class="form-control" value="<?php echo htmlspecialchars($selected_month); ?>">
      </div>
      <div class="col-auto align-self-end">
        <button class="btn btn-primary">表示</button>
      </div>
    </form>

    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ユーザー</th>
          <th>合計時間（時間）</th>
          <th>給与（¥）</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($rows as $r):
            $hours = round($r['minutes'] / 60, 2);
            $rate = isset($r['hourly_rate']) && is_numeric($r['hourly_rate']) ? floatval($r['hourly_rate']) : 1000.00;
            $salary = round($hours * $rate);
        ?>
        <tr>
          <td><?php echo htmlspecialchars($r['username']); ?></td>
          <td><?php echo htmlspecialchars($hours); ?></td>
          <td><?php echo number_format($salary); ?> <small class="text-muted">(時給 <?php echo number_format(round($rate)); ?>)</small></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php render_footer(); ?>

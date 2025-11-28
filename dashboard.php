<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/template.php';

// ログイン確認
if (empty($_SESSION['user_id'])) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$year = intval($_GET['y'] ?? date('Y'));
$month = intval($_GET['m'] ?? date('n'));
if ($month < 1) { $month = 1; }
if ($month > 12) { $month = 12; }

$startOfMonth = date('Y-m-01', strtotime("{$year}-{$month}-01"));
$endOfMonth = date('Y-m-t', strtotime($startOfMonth));

$pdo = getPDO();
if (!empty($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner') {
    $sql = 'SELECT s.*, u.username FROM shifts_scheduled s JOIN users u ON s.user_id = u.user_id WHERE s.shift_date BETWEEN ? AND ? ORDER BY s.shift_date, s.start_time';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$startOfMonth, $endOfMonth]);
} else {
    $sql = 'SELECT s.* FROM shifts_scheduled s WHERE s.user_id = ? AND s.shift_date BETWEEN ? AND ? ORDER BY s.shift_date, s.start_time';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $startOfMonth, $endOfMonth]);
}
$rows = $stmt->fetchAll();

$shifts_by_date = [];
foreach ($rows as $r) {
  $d = $r['shift_date'];
  $start = substr($r['start_time'],0,8);
  $end = substr($r['end_time'],0,8);
  // 日跨ぎ（終業時間が開始時間と同じか前）を判定
  if (strtotime($end) <= strtotime($start)) {
    // 当日分：出勤時間のみ表示（例: 出:22:00）
    $r1 = $r;
    $r1['display_start'] = substr($start,0,5);
    $r1['display_end'] = '';
    $r1['note'] = '(出勤)';
    if (!isset($shifts_by_date[$d])) $shifts_by_date[$d] = [];
    $shifts_by_date[$d][] = $r1;

    // 翌日分：退勤時間のみ表示（例: 退:02:00）
    $nextDate = date('Y-m-d', strtotime($d . ' +1 day'));
    $r2 = $r;
    $r2['display_start'] = '';
    $r2['display_end'] = substr($end,0,5);
    $r2['note'] = '(退勤)';
    if (!isset($shifts_by_date[$nextDate])) $shifts_by_date[$nextDate] = [];
    $shifts_by_date[$nextDate][] = $r2;
  } else {
    $r['display_start'] = substr($start,0,5);
    $r['display_end'] = substr($end,0,5);
    if (!isset($shifts_by_date[$d])) $shifts_by_date[$d] = [];
    $shifts_by_date[$d][] = $r;
  }
}

// 前月・次月
$prev = date('Y-n', strtotime($startOfMonth.' -1 month'));
$next = date('Y-n', strtotime($startOfMonth.' +1 month'));
list($py, $pm) = explode('-', $prev);
list($ny, $nm) = explode('-', $next);

render_header('シフトカレンダー');
?>
<div class="row">
  <div class="col-md-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h1 class="h4"><?php echo htmlspecialchars("{$year}年{$month}月のシフト"); ?></h1>
      <div>
        <a class="btn btn-sm btn-outline-primary me-2" href="<?php echo BASE_PATH; ?>/dashboard.php?y=<?php echo $py; ?>&m=<?php echo $pm; ?>">&lt; 前月</a>
        <a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_PATH; ?>/dashboard.php?y=<?php echo $ny; ?>&m=<?php echo $nm; ?>">次月 &gt;</a>
      </div>
    </div>

    <table class="table table-bordered">
      <thead class="table-light">
        <tr>
          <th>月</th>
          <th>火</th>
          <th>水</th>
          <th>木</th>
          <th>金</th>
          <th>土</th>
          <th>日</th>
        </tr>
      </thead>
      <tbody>
      <?php
      // カレンダー開始（週は月曜始まり）
      $firstWeekday = date('N', strtotime($startOfMonth)); // 1 (Mon) - 7 (Sun)
      $daysInMonth = date('t', strtotime($startOfMonth));

      $cells = [];
      // 空セル
      for ($i = 1; $i < $firstWeekday; $i++) { $cells[] = null; }
      for ($d = 1; $d <= $daysInMonth; $d++) {
          $cells[] = date('Y-m-d', strtotime("{$startOfMonth} +".($d-1).' days'));
      }
      while (count($cells) % 7 !== 0) { $cells[] = null; }

      $rowsCount = intdiv(count($cells), 7);
      for ($r = 0; $r < $rowsCount; $r++) {
          echo "<tr>";
          for ($c = 0; $c < 7; $c++) {
              $idx = $r * 7 + $c;
              $date = $cells[$idx];
              echo '<td class="calendar-cell">';
              if ($date === null) {
                  echo '&nbsp;';
              } else {
                  $dayNum = intval(substr($date,8,2));
                  echo '<div class="fw-bold">'.$dayNum.'</div>';
                  if (!empty($shifts_by_date[$date])) {
                      echo '<ul class="list-unstyled small mb-0">';
                          foreach ($shifts_by_date[$date] as $s) {
                            $ds = $s['display_start'] ?? '';
                            $de = $s['display_end'] ?? '';
                            if ($ds !== '' && $de !== '') {
                              $disp = htmlspecialchars($ds) . ' - ' . htmlspecialchars($de);
                              $liClass = 'shift-normal';
                            } elseif ($ds !== '') {
                              $disp = '出: ' . htmlspecialchars($ds);
                              $liClass = 'shift-start';
                            } else {
                              $disp = '退: ' . htmlspecialchars($de);
                              $liClass = 'shift-end';
                            }
                            $note = isset($s['note']) ? ' <span class="shift-note">'.htmlspecialchars($s['note']).'</span>' : '';
                            if (!empty($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner') {
                              $user = htmlspecialchars($s['username'] ?? '');
                              echo '<li class="'. $liClass . '">' . $disp . $note . ' <span class="text-muted">(' . $user . ')</span></li>';
                            } else {
                              echo '<li class="'. $liClass . '">' . $disp . $note . '</li>';
                            }
                          }
                      echo '</ul>';
                  }
              }
              echo '</td>';
          }
          echo "</tr>";
      }
      ?>
      </tbody>
    </table>
  </div>
</div>

<?php render_footer(); ?>

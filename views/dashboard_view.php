<?php render_header('シフトカレンダー'); ?>
<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">ダッシュボード</h1>
    </div>

    <!-- お知らせセクション -->
    <?php if (!empty($announcements)): ?>
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <i class="bi bi-megaphone-fill me-2"></i>お知らせ
        </div>
        <div class="list-group list-group-flush">
            <?php foreach ($announcements as $news): ?>
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 fw-bold"><?php echo h($news['title']); ?></h6>
                    <small class="text-muted"><?php echo date('Y/m/d H:i', strtotime($news['created_at'])); ?></small>
                </div>
                <p class="mb-1 small text-muted" style="white-space: pre-wrap;"><?php echo h($news['content']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
      <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
          <h1 class="h4"><?php echo htmlspecialchars("{$year}年{$month}月のシフト"); ?></h1>
          <div>
            <a class="btn btn-sm btn-outline-primary me-2" href="<?php echo BASE_PATH; ?>/dashboard.php?y=<?php echo htmlspecialchars($py); ?>&m=<?php echo htmlspecialchars($pm); ?>">&lt; 前月</a>
            <a class="btn btn-sm btn-outline-primary" href="<?php echo BASE_PATH; ?>/dashboard.php?y=<?php echo htmlspecialchars($ny); ?>&m=<?php echo htmlspecialchars($nm); ?>">次月 &gt;</a>
          </div>
        </div>

    <table class="table table-bordered calendar-table">
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
              
              $is_holiday = ($date !== null && in_array($date, $holidays));
              $bg_class = $is_holiday ? 'bg-light text-muted' : '';
              
              echo '<td class="calendar-cell ' . $bg_class . '">';
              if ($date === null) {
                  echo '&nbsp;';
              } else {
                  $dayNum = intval(substr($date,8,2));
                  echo '<div class="fw-bold">'.$dayNum.'</div>';
                  
                  if ($is_holiday) {
                      echo '<div class="badge bg-secondary mb-1">定休日</div>';
                  }
                  
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

  </div>
</div>
</div>

<?php render_footer(); ?>

<?php
/**
 * 確定シフト一覧画面のビュー
 * 
 * 確定したシフトの一覧を表形式で表示します。
 * 日跨ぎシフトの場合は、分割された状態で表示されます。
 */
render_header('確定シフト', true, ['dashboard.css', 'calendar.css']); ?>
<?php
// 前月・次月リンク用
$prev_month = date('Y-m', strtotime($min_date . ' -1 month'));
$next_month = date('Y-m', strtotime($min_date . ' +1 month'));

// シフトデータを日付キーに整理
$schedule_map = [];
foreach ($display_schedules as $row) {
    // 日跨ぎ分割済みのデータを使用
    $d = $row['shift_date'];
    if (!isset($schedule_map[$d])) $schedule_map[$d] = [];
    $schedule_map[$d][] = $row;
}
?>

<div class="row justify-content-center">
  <div class="col-12 col-md-10 col-lg-8">
    
    <!-- Month Navigation -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="?m=<?php echo $prev_month; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-chevron-left"></i> 前月</a>
        <h4 class="mb-0 fw-bold"><?php echo htmlspecialchars($target_year_month); ?></h4>
        <a href="?m=<?php echo $next_month; ?>" class="btn btn-outline-secondary btn-sm">次月 <i class="bi bi-chevron-right"></i></a>
    </div>

    <!-- Desktop Table View -->
    <div class="d-none d-md-block">
        <?php if(empty($display_schedules)): ?>
          <div class="alert alert-info text-center">確定されたシフトはありません。</div>
        <?php else: ?>
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>日付</th>
                    <th>時間</th>
                    <th>備考</th>
                    <th>登録日</th>
                  </tr>
                </thead>
                <tbody>
                <?php foreach($display_schedules as $row): ?>
                  <tr>
                    <td class="fw-bold"><?php echo htmlspecialchars(date('j日', strtotime($row['shift_date']))) . ' ' . get_day_of_week_ja($row['shift_date']); ?></td>
                    <td>
                        <i class="bi bi-clock me-1 text-muted"></i>
                        <?php echo htmlspecialchars($row['display_start']); ?> - <?php echo htmlspecialchars($row['display_end']); ?>
                    </td>
                    <td><?php echo htmlspecialchars($row['note'] ?? '-'); ?></td>
                    <td class="small text-muted"><?php echo htmlspecialchars(date('m/d H:i', strtotime($row['created_at']))); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>
    </div>

    <!-- Mobile Dot Grid View -->
    <div class="d-md-none">
        <!-- Weekday Header -->
        <div class="d-flex text-center mb-2 fw-bold text-muted small">
            <div style="flex:1; color: var(--danger-color);">日</div>
            <div style="flex:1">月</div>
            <div style="flex:1">火</div>
            <div style="flex:1">水</div>
            <div style="flex:1">木</div>
            <div style="flex:1">金</div>
            <div style="flex:1; color: var(--primary-color);">土</div>
        </div>

        <div class="mobile-calendar-grid mb-4">
            <?php
            $firstWeekday = date('w', strtotime($min_date)); 
            for ($i = 0; $i < $firstWeekday; $i++) echo '<div class="mobile-grid-cell empty"></div>';

            $targetDays = date('t', strtotime($min_date));
            
            for ($d = 1; $d <= $targetDays; $d++) {
                $currentDate = date('Y-m-d', strtotime($min_date . " +" . ($d - 1) . " days"));
                $is_holiday = in_array($currentDate, $holidays);
                $dayOfWeek = date('w', strtotime($currentDate));
                $shifts = $schedule_map[$currentDate] ?? [];
                $has_shift = !empty($shifts);
                
                $cellClass = 'mobile-grid-cell';
                $dowClass = 'dow-' . $dayOfWeek;
                
                // Detail Data
                $detailData = [
                    'dateStr' => intval(substr($currentDate, 8, 2)) . '日 (' . ['日','月','火','水','木','金','土'][$dayOfWeek] . ')',
                    'fullDate' => $currentDate,
                    'isHoliday' => $is_holiday,
                    'shifts' => []
                ];
                foreach($shifts as $s){
                    $detailData['shifts'][] = [
                        'time' => $s['display_start'] . ' - ' . $s['display_end'],
                        'note' => $s['note']
                    ];
                }
                $jsonData = htmlspecialchars(json_encode($detailData), ENT_QUOTES, 'UTF-8');
            ?>
            <div class="<?php echo $cellClass; ?> <?php echo $dowClass; ?>" 
                 onclick="selectScheduleDate(this, <?php echo $jsonData; ?>)">
                <span class="mobile-grid-date"><?php echo intval(substr($currentDate, 8, 2)); ?></span>
                <div class="mobile-grid-dots">
                    <?php if ($is_holiday): ?>
                        <div class="mobile-dot dot-holiday"></div>
                    <?php endif; ?>
                    <?php if ($has_shift): ?>
                        <div class="mobile-dot dot-approved"></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Mobile Detail Area -->
        <div class="mobile-detail-area text-center" id="scheduleDetailArea">
            <div class="mobile-detail-header" id="scheduleDetailTitle">日付を選択してください</div>
            <div id="scheduleDetailContent" class="text-start">
                <p class="text-muted small text-center">日付をタップするとシフト詳細が表示されます。</p>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 mb-5">
      <a class="btn btn-outline-secondary" href="<?php echo BASE_PATH; ?>/parttime/submit_shift.php">
        <i class="bi bi-calendar-plus me-1"></i> 希望提出に戻る
      </a>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-select today or first day
    if (window.innerWidth < 768) {
        var today = '<?php echo date("Y-m-d"); ?>';
        // Try to click today first, else first cell
        // But only if today is in this month
        // Simpler: just click first non-empty cell? Or verify if today is in range.
        
        // Let's just default to first cell for simplicity in this iteration
        var firstCell = document.querySelector('.mobile-grid-cell:not(.empty)');
        if(firstCell) firstCell.click();
    }
});

function selectScheduleDate(element, data) {
    document.querySelectorAll('.mobile-grid-cell').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    document.getElementById('scheduleDetailTitle').textContent = data.dateStr;
    
    var contentHtml = '';
    
    if (data.isHoliday) {
        contentHtml += '<div class="alert alert-danger py-2 mb-2"><i class="bi bi-x-circle me-1"></i>定休日</div>';
    }
    
    if (data.shifts.length > 0) {
        contentHtml += '<div class="list-group list-group-flush">';
        data.shifts.forEach(s => {
            contentHtml += `<div class="list-group-item px-0 bg-transparent">
                <div class="ls-shift-entry">
                    <span class="h5 mb-0 text-success"><i class="bi bi-clock-history me-2"></i>${s.time}</span>
                    ${s.note ? `<div class="text-muted small mt-1">${s.note}</div>` : ''}
                </div>
            </div>`;
        });
        contentHtml += '</div>';
    } else if (!data.isHoliday) {
        contentHtml += '<p class="text-muted mb-2 text-center py-3">シフトはありません</p>';
    }
    
    document.getElementById('scheduleDetailContent').innerHTML = contentHtml;
}
</script>

<?php render_footer(); ?>

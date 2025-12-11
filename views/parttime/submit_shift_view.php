<?php
/**
 * 希望シフト提出画面のビュー
 * 
 * ユーザーが希望するシフトの日付、開始・終了時刻を入力するフォームを表示します。
 * エラーメッセージや成功メッセージがある場合はそれらも表示します。
 */
render_header('希望シフト提出'); ?>
<!-- FullCalendar CDN -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>

<div class="row justify-content-center">
  <div class="col-md-10 col-lg-8"> <!-- カレンダー用に幅を広げる -->
    <div class="card shadow-lg border-0 my-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h4 class="mb-0 fw-bold"><i class="bi bi-calendar-check me-2"></i>希望シフト提出</h4>
        </div>
        <div class="card-body p-4">
            
            <?php if(!empty($deadline_msg)): ?>
                <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis mb-4">
                    <div class="d-flex">
                        <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                        <div>
                            <strong><?php echo htmlspecialchars($target_year_month); ?>分</strong>のシフト提出を受け付けています。<br>
                            <?php echo htmlspecialchars($deadline_msg); ?>
                             <?php if(!empty($holiday_msg)): ?>
                                <br><small class="text-danger mt-1 d-block"><i class="bi bi-exclamation-triangle-fill me-1"></i><?php echo htmlspecialchars($holiday_msg); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if(!empty($error)): ?>
                <div class="alert alert-danger border-0 d-flex align-items-center mb-4">
                    <i class="bi bi-exclamation-circle-fill me-2"></i>
                    <div><?php echo htmlspecialchars($error); ?></div>
                </div>
            <?php endif; ?>
            
            <?php if(!empty($success)): ?>
                <div class="alert alert-success border-0 d-flex align-items-center mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div><?php echo htmlspecialchars($success); ?></div>
                </div>
            <?php endif; ?>

            <!-- 凡例 -->
            <div class="d-flex justify-content-end mb-3 align-items-center small">
                <span class="me-2 text-muted fw-bold">ステータス:</span>
                <span class="badge bg-primary me-2"><i class="bi bi-hourglass-split me-1"></i>申請中</span>
                <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i>承認済</span>
            </div>
            
<?php
// Organize shifts by date for the custom mobile grid
$shifts_by_date = [];
if (!empty($requested_shifts)) {
    foreach ($requested_shifts as $s) {
        $d = $s['shift_date'];
        if (!isset($shifts_by_date[$d])) $shifts_by_date[$d] = [];
        // Format for display
        $s['start_fmt'] = substr($s['start_time'], 0, 5);
        $s['end_fmt'] = substr($s['end_time'], 0, 5);
        $shifts_by_date[$d][] = $s;
    }
}
?>

<!-- Desktop Calendar Wrapper -->
<div class="d-none d-md-block">
    <div id='calendar'></div>
</div>

<!-- Mobile Dot Grid Calendar -->
<div class="d-md-none">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($target_year_month); ?></h5>
        <span class="badge bg-secondary">希望提出用</span>
    </div>

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

    <div class="mobile-calendar-grid">
        <?php
        $firstWeekday = date('w', strtotime($min_date)); // 0 (Sun) - 6 (Sat)
        // Empty cells for start padding
        for ($i = 0; $i < $firstWeekday; $i++) { 
            echo '<div class="mobile-grid-cell empty"></div>';
        }

        $targetDays = date('t', strtotime($min_date)); // Days in target month
        
        for ($d = 1; $d <= $targetDays; $d++) {
            $currentDate = date('Y-m-d', strtotime($min_date . " +" . ($d - 1) . " days"));
            $is_holiday = in_array($currentDate, $holidays);
            $dayOfWeek = date('w', strtotime($currentDate));
            
            $shifts = $shifts_by_date[$currentDate] ?? [];
            $has_shift = !empty($shifts);
            
            // Determine Dot Class based on status
            // If any shift is approved -> Green
            // Else if pending -> Blue
            // If holiday -> Red (handled separately)
            $dotClass = '';
            if ($has_shift) {
                $is_approved = false;
                foreach($shifts as $s){
                    if($s['request_status'] === 'approved') {
                        $is_approved = true;
                        break;
                    }
                }
                $dotClass = $is_approved ? 'dot-approved' : 'dot-pending';
            }
            
            $cellClass = 'mobile-grid-cell';
            $dowClass = 'dow-' . $dayOfWeek;

            // Prepare Data for Detail View
            // Using JSON for cleaner handling in JS
            $detailData = [
                'dateStr' => intval(substr($currentDate, 8, 2)) . '日 (' . ['日','月','火','水','木','金','土'][$dayOfWeek] . ')',
                'fullDate' => $currentDate,
                'isHoliday' => $is_holiday,
                'shifts' => []
            ];
            foreach($shifts as $s){
                $detailData['shifts'][] = [
                    'time' => $s['start_fmt'] . ' - ' . $s['end_fmt'],
                    'status' => ($s['request_status'] == 'approved') ? '承認済' : '申請中',
                    'statusClass' => ($s['request_status'] == 'approved') ? 'text-success' : 'text-primary'
                ];
            }
            $jsonData = htmlspecialchars(json_encode($detailData), ENT_QUOTES, 'UTF-8');
        ?>
        <div class="<?php echo $cellClass; ?> <?php echo $dowClass; ?>" 
             onclick="selectMobileDate(this, <?php echo $jsonData; ?>)"
             id="m-cell-<?php echo $currentDate; ?>">
            
            <span class="mobile-grid-date"><?php echo intval(substr($currentDate, 8, 2)); ?></span>
            
            <div class="mobile-grid-dots">
                <?php if ($is_holiday): ?>
                    <div class="mobile-dot dot-holiday"></div>
                <?php endif; ?>
                <?php if ($has_shift): ?>
                    <div class="mobile-dot <?php echo $dotClass; ?>"></div>
                <?php endif; ?>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Mobile Detail Area -->
    <div class="mobile-detail-area text-center" id="mobileDetailArea">
        <div class="mobile-detail-header" id="mobileDetailTitle">日付を選択してください</div>
        <div id="mobileDetailContent" class="text-start mb-3">
            <!-- Content injected by JS -->
            <p class="text-muted small text-center">カレンダーの日付をタップすると詳細が表示されます。</p>
        </div>
        
        <!-- Add Shift Button (Initially hidden/disabled) -->
        <button id="mobileAddBtn" class="btn btn-primary w-100" disabled onclick="openMobileModal()">
            <i class="bi bi-plus-circle me-1"></i>この日のシフトを希望する
        </button>
    </div>
</div>

            <?php if (!empty($is_past_deadline) && $is_past_deadline): ?>
                <div class="text-center text-muted mt-3">
                    <p>次回の提出期間をお待ちください。</p>
                </div>
            <?php endif; ?>
            
        </div>
    </div>
  </div>
</div>

<!-- シフト提出モーダル -->
<div class="modal fade" id="shiftModal" tabindex="-1" aria-labelledby="shiftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title fw-bold" id="shiftModalLabel">シフト提出: <span id="modalDateDisplay"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form method="post" id="shiftForm" class="needs-validation" novalidate>
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
            
            <div class="mb-3">
            <label for="shift_template" class="form-label fw-bold text-secondary">シフトパターンから選択</label>
            <select id="shift_template" class="form-select">
                <option value="">-- パターンを選択してください --</option>
                <?php foreach($templates as $tpl): ?>
                    <option value="<?php echo htmlspecialchars($tpl['template_id']); ?>" 
                            data-start="<?php echo htmlspecialchars(substr($tpl['start_time'], 0, 5)); ?>" 
                            data-end="<?php echo htmlspecialchars(substr($tpl['end_time'], 0, 5)); ?>">
                        <?php echo htmlspecialchars($tpl['template_name']); ?> (<?php echo htmlspecialchars(substr($tpl['start_time'], 0, 5)); ?> - <?php echo htmlspecialchars(substr($tpl['end_time'], 0, 5)); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            </div>

            <div class="mb-3">
            <label for="shift_date" class="form-label fw-bold text-secondary">日付</label>
            <input type="date" id="shift_date" name="shift_date" class="form-control" readonly required>
            </div>
            
            <div class="row g-3 mb-3">
                <div class="col-6">
                <label for="start_time" class="form-label fw-bold text-secondary">開始時刻 <span class="badge bg-danger ms-1">必須</span></label>
                <input type="time" id="start_time" name="start_time" class="form-control" required>
                </div>
                <div class="col-6">
                <label for="end_time" class="form-label fw-bold text-secondary">終了時刻 <span class="badge bg-danger ms-1">必須</span></label>
                <input type="time" id="end_time" name="end_time" class="form-control" required>
                </div>
            </div>
            
            <div class="d-grid mt-4">
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-send-fill me-2"></i>この内容で提出する
                </button>
            </div>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- Mobile Floating Action Button (Removed as requested to use Grid UI) -->

<script>
// Global state for mobile selection
var selectedMobileDate = null;
var isPastDeadline = <?php echo json_encode($is_past_deadline); ?>;

document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    var shiftModal = new bootstrap.Modal(document.getElementById('shiftModal'));
    var minDate = '<?php echo $min_date; ?>';
    var maxDate = '<?php echo $max_date; ?>';
    
    // Desktop Calendar Initialization
    if (calendarEl && window.innerWidth >= 768) {
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            initialDate: minDate,
            locale: 'ja',
            headerToolbar: {
                left: 'title',
                center: '',
                right: 'today prev,next'
            },
            height: 'auto',
            contentHeight: 'auto',
            events: <?php echo $json_events; ?>,
            dateClick: function(info) {
                if (isPastDeadline) {
                    alert('提出期間外です。');
                    return;
                }
                if (info.dateStr < minDate || info.dateStr > maxDate) return;
                
                openModalWithDate(info.dateStr, shiftModal);
            }
        });
        calendar.render();
    }
    
    // Auto-select first available day on mobile
    if (window.innerWidth < 768) {
        // Find first day
        var firstCell = document.querySelector('.mobile-grid-cell:not(.empty)');
        if(firstCell) firstCell.click();
    }

    // シフトパターン選択連動
    document.getElementById('shift_template').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const start = selectedOption.getAttribute('data-start');
        const end = selectedOption.getAttribute('data-end');
        if (start && end) {
            document.getElementById('start_time').value = start;
            document.getElementById('end_time').value = end;
        }
    });

    // Make modal global for access
    window.shiftModal = shiftModal;
});

function selectMobileDate(element, data) {
    // UI Update
    document.querySelectorAll('.mobile-grid-cell').forEach(el => el.classList.remove('active'));
    element.classList.add('active');
    
    // Update State
    selectedMobileDate = data.fullDate;
    
    // Update Detail Area
    document.getElementById('mobileDetailTitle').textContent = data.dateStr;
    
    var contentHtml = '';
    
    if (data.isHoliday) {
        contentHtml += '<div class="alert alert-danger py-2 mb-2"><i class="bi bi-x-circle me-1"></i>定休日です</div>';
    }
    
    if (data.shifts.length > 0) {
        contentHtml += '<ul class="list-group list-group-flush mb-2">';
        data.shifts.forEach(s => {
            contentHtml += `<li class="list-group-item px-0">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">${s.time}</span>
                    <span class="${s.statusClass}">${s.status}</span>
                </div>
            </li>`;
        });
        contentHtml += '</ul>';
    } else if (!data.isHoliday) {
        contentHtml += '<p class="text-muted mb-2">提出されたシフトはありません。</p>';
    }
    
    document.getElementById('mobileDetailContent').innerHTML = contentHtml;
    
    // Update Button State
    var btn = document.getElementById('mobileAddBtn');
    if (isPastDeadline || data.isHoliday) {
        btn.disabled = true;
        btn.textContent = data.isHoliday ? '定休日のため提出不可' : '期間外のため提出不可';
    } else {
        btn.disabled = false;
        btn.textContent = 'この日のシフトを希望する';
    }
}

function openMobileModal() {
    if(!selectedMobileDate) return;
    openModalWithDate(selectedMobileDate, window.shiftModal);
}

function openModalWithDate(dateStr, modalInstance) {
    document.getElementById('shift_date').value = dateStr;
    document.getElementById('shift_date').readOnly = true; 
    document.getElementById('modalDateDisplay').textContent = dateStr;
    document.getElementById('start_time').value = '';
    document.getElementById('end_time').value = '';
    document.getElementById('shift_template').value = '';
    modalInstance.show();
}
</script>

<?php render_footer(); ?>

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
            <?php 
            $first_news = $announcements[0];
            $other_news = array_slice($announcements, 1);
            ?>
            <!-- 最新の1件 -->
            <div class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h6 class="mb-1 fw-bold"><?php echo h($first_news['title']); ?></h6>
                    <small class="text-muted"><?php echo date('Y/m/d H:i', strtotime($first_news['created_at'])); ?></small>
                </div>
                <p class="mb-1 small text-muted" style="white-space: pre-wrap;"><?php echo h($first_news['content']); ?></p>
            </div>

            <?php if (!empty($other_news)): ?>
            <!-- 2件目以降は折りたたみ -->
            <div class="collapse" id="newsCollapse">
                <?php foreach ($other_news as $news): ?>
                <div class="list-group-item">
                    <div class="d-flex w-100 justify-content-between">
                        <h6 class="mb-1 fw-bold"><?php echo h($news['title']); ?></h6>
                        <small class="text-muted"><?php echo date('Y/m/d H:i', strtotime($news['created_at'])); ?></small>
                    </div>
                    <p class="mb-1 small text-muted" style="white-space: pre-wrap;"><?php echo h($news['content']); ?></p>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="card-footer text-center p-0">
                <button class="btn btn-link text-decoration-none w-100 py-2" type="button" data-bs-toggle="collapse" data-bs-target="#newsCollapse" aria-expanded="false" aria-controls="newsCollapse">
                    <i class="bi bi-chevron-down me-1"></i>もっと見る
                </button>
            </div>
            <?php endif; ?>
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

    <!-- PC Table View (d-none d-md-block) -->
    <div class="d-none d-md-block">
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

    <!-- Mobile Grid View (d-md-none) -->
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

        <div class="mobile-calendar-grid">
            <?php
            // Empty cells for start padding
            for ($i = 1; $i < $firstWeekday; $i++) { 
                echo '<div class="mobile-grid-cell empty"></div>';
            }

            $firstDateStr = ''; // To auto-select first day
            
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $currentDate = date('Y-m-d', strtotime("{$startOfMonth} +".($d-1).' days'));
                
                if ($d === 1) $firstDateStr = $currentDate;

                $is_today = ($currentDate === date('Y-m-d'));
                $is_holiday = in_array($currentDate, $holidays);
                $dayOfWeek = date('w', strtotime($currentDate));
                
                $shifts = $shifts_by_date[$currentDate] ?? [];
                $has_shift = !empty($shifts);
                
                $cellClass = 'mobile-grid-cell';
                if ($is_today) $cellClass .= ' today';
                $dowClass = 'dow-' . $dayOfWeek;

                // Prepare Data Attributes for JS
                $detailHtml = '';
                if ($is_holiday) {
                    $detailHtml .= '<div class="badge bg-danger mb-2">定休日</div>';
                }
                if ($has_shift) {
                    $detailHtml .= '<ul class="list-unstyled mb-0">';
                    foreach ($shifts as $s) {
                        $ds = $s['display_start'] ?? '';
                        $de = $s['display_end'] ?? '';
                        if ($ds && $de) $t = htmlspecialchars($ds) . ' - ' . htmlspecialchars($de);
                        elseif($ds) $t = '出勤: ' . htmlspecialchars($ds);
                        else $t = '退勤: ' . htmlspecialchars($de);
                        
                        $note = isset($s['note']) ? htmlspecialchars($s['note']) : '';
                        $user = (!empty($_SESSION['user_type']) && $_SESSION['user_type'] === 'owner') ? htmlspecialchars($s['username'] ?? '') : '';
                        
                        $detailHtml .= '<li class="mb-2 p-2 bg-light rounded">';
                        $detailHtml .= '<strong>'.$t.'</strong>';
                        if($user) $detailHtml .= ' <span class="text-muted small">('.$user.')</span>';
                        if($note) $detailHtml .= '<div class="small text-muted mt-1">'.$note.'</div>';
                        $detailHtml .= '</li>';
                    }
                    $detailHtml .= '</ul>';
                } elseif (!$is_holiday) {
                    $detailHtml .= '<div class="text-muted">予定はありません</div>';
                }
            ?>
            <div class="<?php echo $cellClass; ?> <?php echo $dowClass; ?>" 
                 onclick="selectDate(this, '<?php echo intval(substr($currentDate, 8, 2)); ?>日 (<?php echo ['日','月','火','水','木','金','土'][$dayOfWeek]; ?>)', '<?php echo htmlspecialchars($detailHtml, ENT_QUOTES); ?>')"
                 id="cell-<?php echo $currentDate; ?>">
                
                <span class="mobile-grid-date"><?php echo intval(substr($currentDate, 8, 2)); ?></span>
                
                <div class="mobile-grid-dots">
                    <?php if ($is_holiday): ?>
                        <div class="mobile-dot dot-holiday"></div>
                    <?php endif; ?>
                    <?php if ($has_shift): ?>
                        <div class="mobile-dot dot-shift"></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php } ?>
        </div>

        <!-- Detail Area -->
        <div class="mobile-detail-area" id="mobileDetailArea">
            <div class="mobile-detail-header" id="mobileDetailTitle">日付を選択してください</div>
            <div class="mobile-detail-content" id="mobileDetailContent">
                カレンダーの日付をタップすると詳細が表示されます。
            </div>
        </div>
    </div>

    <script>
    function selectDate(element, dateStr, contentHtml) {
        // Remove active class from all
        document.querySelectorAll('.mobile-grid-cell').forEach(el => el.classList.remove('active'));
        // Add active to clicked
        element.classList.add('active');
        
        // Update detail area
        document.getElementById('mobileDetailTitle').textContent = dateStr;
        
        // Decode HTML entities if needed (simple assignment handles standard tags)
        // Since we passed escaped HTML in 'onclick', we need to be careful.
        // Or simpler: put data in hidden divs and clone them? 
        // Current approach: Using a textarea hack or just innerHTML if we trust the source (our own PHP).
        // Since attributes are ENT_QUOTES escaped, we need to unescape slightly to set innerHTML properly?
        // Actually, rendering formatted HTML into a data-attribute is messy.
        // Let's use a simpler approach: Hidden Content Blocks.
    
        // Re-implementing with Hidden Content Blocks below:
    }
    </script>
    
    <!-- Redoing the Loop logic below for cleaner JS interaction -->
    <div style="display:none;">
        <?php foreach($shifts_by_date as $date => $shifts): /* Loop again or use previous loop to generate hidden divs */ ?>
        <?php endforeach; ?>
    </div>
    
    <!-- REPLACING SCRIPT WITH ROBUST VERSION -->
    <script>
    function selectDate(element, dateTitle, contentEncoded) {
        document.querySelectorAll('.mobile-grid-cell').forEach(el => el.classList.remove('active'));
        element.classList.add('active');
        
        document.getElementById('mobileDetailTitle').textContent = dateTitle;
        
        // Decode the content (it was htmlspecialchars encoded)
        var txt = document.createElement('textarea');
        txt.innerHTML = contentEncoded;
        document.getElementById('mobileDetailContent').innerHTML = txt.value;
    }
    
    // Auto-select today or first day
    document.addEventListener('DOMContentLoaded', function() {
        var today = '<?php echo date('Y-m-d'); ?>';
        var el = document.getElementById('cell-' + today);
        if (!el) {
            // First day of month
            el = document.querySelector('.mobile-grid-cell:not(.empty)');
        }
        if (el) el.click();
    });
    </script>

  </div>
</div>

  </div>
</div>
</div>

<?php render_footer(); ?>

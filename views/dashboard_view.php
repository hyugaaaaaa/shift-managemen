<?php render_header('シフトカレンダー'); ?>
<div class="page-container">
    <div class="page-header">
        <h1 class="page-title">ダッシュボード</h1>
    </div>

    <!-- お知らせセクション -->
    <?php if (!empty($company_code)): ?>
    <div class="alert alert-warning d-flex align-items-center shadow-sm" role="alert">
        <i class="bi bi-star-fill me-2 text-warning fs-4"></i>
        <div>
            <strong>あなたの企業の招待コード:</strong> <span class="badge bg-dark fs-6 font-monospace ms-2 user-select-all"><?php echo htmlspecialchars($company_code); ?></span>
            <span class="ms-2 small">このコードをスタッフに伝えて、登録を案内してください。</span>
        </div>
    </div>
    <?php endif; ?>

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

    <!-- PC View (d-none d-md-block) -->
    <div class="d-none d-md-block">
        <div class="row">
            <!-- Left Column: Calendar -->
            <div class="col-md-9">
                <table class="table table-bordered calendar-table h-100" id="pcCalendarTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-danger">日</th>
                            <th>月</th>
                            <th>火</th>
                            <th>水</th>
                            <th>木</th>
                            <th>金</th>
                            <th class="text-primary">土</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    // カレンダー生成
                    $firstWeekday = date('w', strtotime($startOfMonth)); // 0 (Sun) - 6 (Sat)
                    $daysInMonth = date('t', strtotime($startOfMonth));
                    
                    $cells = [];
                    // 空セル (日曜始まりの場合、$firstWeekday個の空セル)
                    for ($i = 0; $i < $firstWeekday; $i++) { $cells[] = null; }
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
                            $cursor_class = $date ? 'cursor-pointer' : '';

                            // 今日の日付判定
                            if ($date === date('Y-m-d')) {
                                $bg_class .= ' bg-info bg-opacity-10'; // 今日は薄い青
                            }
                            
                            echo '<td class="calendar-cell ' . $bg_class . ' ' . $cursor_class . '" ';
                            if ($date) {
                                echo 'onclick="selectDatePC(this, \'' . $date . '\')" id="pc-cell-'.$date.'"';
                            }
                            echo '>';
                            
                            if ($date === null) {
                                echo '&nbsp;';
                            } else {
                                $dayNum = intval(substr($date,8,2));
                                echo '<div class="fw-bold mb-1">'.$dayNum.'</div>';
                                
                                if ($is_holiday) {
                                    echo '<div class="badge bg-secondary mb-1">定休日</div>';
                                }
                                
                                if (!empty($shifts_by_date[$date])) {
                                    // シフトあり表示（件数またはドット）
                                    if ($_SESSION['user_type'] === 'owner') {
                                        // ユニークなユーザー数を表示
                                        $uniqueCount = isset($unique_users_by_date[$date]) ? count($unique_users_by_date[$date]) : 0;
                                        echo '<div class="text-primary small fw-bold"><i class="bi bi-people-fill"></i> ' . $uniqueCount . '名</div>';
                                    } else {
                                        // 自分のシフトのみバッジ表示
                                        foreach ($shifts_by_date[$date] as $s) {
                                            if ($s['user_id'] != $_SESSION['user_id']) continue; // 他人のシフトはカレンダーセルには表示しない

                                            $ds = $s['display_start'] ?? '';
                                            $de = $s['display_end'] ?? '';
                                            if ($ds) echo '<div class="badge bg-primary text-wrap text-start mb-1">出:'.$ds.'</div>';
                                            if ($de) echo '<div class="badge bg-danger text-wrap text-start">退:'.$de.'</div>';
                                        }
                                    }
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

            <!-- Right Column: Detail Panel -->
            <div class="col-md-3">
                <div class="card shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <i class="bi bi-calendar-event me-2"></i><span id="detailTitle">日付を選択</span>
                    </div>
                    <div class="card-body" id="detailContent" style="min-height: 300px; max-height: 80vh; overflow-y: auto;">
                        <p class="text-muted text-center py-5">カレンダーの日付をクリックすると<br>詳細が表示されます。</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // PHPからデータをJSONとして受け渡す
        const shiftsData = <?php echo json_encode($shifts_by_date, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const holidaysData = <?php echo json_encode($holidays, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
        const currentUserId = <?php echo json_encode($_SESSION['user_id']); ?>;

        function selectDatePC(element, dateStr) {
            // アクティブスタイルの切り替え
            document.querySelectorAll('#pcCalendarTable td').forEach(el => el.classList.remove('calendar-selected'));
            element.classList.add('calendar-selected'); // 選択状態を強調

            // タイトル更新: YYYY-MM-DD -> DD日 (曜日)
            const dateObj = new Date(dateStr);
            const dayOfWeek = ['日', '月', '火', '水', '木', '金', '土'][dateObj.getDay()];
            const formattedDate = dateObj.getDate() + '日 (' + dayOfWeek + ')';
            document.getElementById('detailTitle').textContent = formattedDate;

            // コンテンツ生成
            const container = document.getElementById('detailContent');
            let html = '';

            // 定休日チェック
            if (holidaysData.includes(dateStr)) {
                html += '<div class="alert alert-secondary mb-3"><i class="bi bi-shop me-2"></i>定休日です</div>';
            }

            const shifts = shiftsData[dateStr];
            if (shifts && shifts.length > 0) {
                html += '<div class="list-group list-group-flush">';
                shifts.forEach(s => {
                    const ds = s.display_start || '';
                    const de = s.display_end || '';
                    const note = s.note || '';
                    const username = s.username || '';
                    const isMe = (s.user_id == currentUserId);
                    const bgClass = isMe ? 'bg-primary-subtle' : '';

                    html += '<div class="list-group-item px-2 ' + bgClass + '">';
                    
                    // 名前は全員に表示 (自分には(あなた)をつける)
                    let nameDisplay = username;
                    if (isMe) nameDisplay += ' <span class="badge bg-primary ms-1">あなた</span>';

                    html += '<div class="fw-bold mb-1"><i class="bi bi-person-circle me-1"></i>' + nameDisplay + '</div>';

                    if (ds && de) {
                        html += '<div class="text-dark ms-3">' + ds + ' - ' + de + '</div>';
                    } else if (ds) {
                        html += '<div class="text-primary ms-3">出勤: ' + ds + '</div>';
                    } else if (de) {
                        html += '<div class="text-danger ms-3">退勤: ' + de + '</div>';
                    }

                    if (note) {
                        html += '<div class="small text-muted mt-1 ms-3"><i class="bi bi-info-circle me-1"></i>' + note + '</div>';
                    }
                    html += '</div>';
                });
                html += '</div>';
            } else {
                if (!holidaysData.includes(dateStr)) {
                    html += '<div class="text-center text-muted py-4">シフト・予定はありません</div>';
                }
            }
            container.innerHTML = html;
        }
        
        // 初期表示時、今日の日付があれば選択、なければ1日を選択
        document.addEventListener('DOMContentLoaded', function() {
            const today = '<?php echo date("Y-m-d"); ?>';
            const startOfMonth = '<?php echo $startOfMonth; ?>';
            const todayCell = document.getElementById('pc-cell-' + today);
            
            if (todayCell) {
                todayCell.click();
            } else {
                // 今月表示中で今日が含まれない場合は1日を選択
                const firstDayCell = document.getElementById('pc-cell-' + startOfMonth);
                if (firstDayCell) firstDayCell.click();
            }
        });
        </script>
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
                
                // ここでの $shifts は全員分なので、Mobile Gridのドットやデータ属性用に処理を分ける
                $my_shifts = [];
                foreach($shifts as $s) {
                    if($s['user_id'] == $_SESSION['user_id']) {
                        $my_shifts[] = $s;
                    }
                }
                $has_my_shift = !empty($my_shifts); // ドット表示用（自分のシフトがあるか）
                $has_any_shift = !empty($shifts); // 詳細表示用（誰かしらシフトがあるか）

                
                $cellClass = 'mobile-grid-cell';
                if ($is_today) $cellClass .= ' today';
                $dowClass = 'dow-' . $dayOfWeek;

                // Prepare Data Attributes for JS (All Shifts)
                $detailHtml = '';
                if ($is_holiday) {
                    $detailHtml .= '<div class="badge bg-danger mb-2">定休日</div>';
                }
                if ($has_any_shift) {
                    $detailHtml .= '<ul class="list-unstyled mb-0">';
                    foreach ($shifts as $s) {
                        $ds = $s['display_start'] ?? '';
                        $de = $s['display_end'] ?? '';
                        if ($ds && $de) $t = htmlspecialchars($ds) . ' - ' . htmlspecialchars($de);
                        elseif($ds) $t = '出勤: ' . htmlspecialchars($ds);
                        else $t = '退勤: ' . htmlspecialchars($de);
                        
                        $note = isset($s['note']) ? htmlspecialchars($s['note']) : '';
                        $user = htmlspecialchars($s['username'] ?? '');
                        // 自分かどうか
                        $isMe = ($s['user_id'] == $_SESSION['user_id']);
                        $bgStyle = $isMe ? 'background-color: #e3f2fd;' : 'bg-light'; // 自分の場合は薄い青
                        
                        $detailHtml .= '<li class="mb-2 p-2 rounded" style="'.$bgStyle.'">';
                        $detailHtml .= '<div class="fw-bold small"><i class="bi bi-person-circle me-1"></i>'.$user;
                        if($isMe) $detailHtml .= ' <span class="badge bg-primary ms-1">あなた</span>';
                        $detailHtml .= '</div>';
                        
                        $detailHtml .= '<div class="ps-3">';
                        $detailHtml .= '<strong>'.$t.'</strong>';
                        if($note) $detailHtml .= '<div class="small text-muted mt-1">'.$note.'</div>';
                        $detailHtml .= '</div>';
                        
                        $detailHtml .= '</li>';
                    }
                    $detailHtml .= '</ul>';
                } elseif (!$is_holiday) {
                    $detailHtml .= '<div class="text-muted">予定はありません</div>';
                }
            ?>
            <div class="<?php echo $cellClass; ?> <?php echo $dowClass; ?>" 
                 onclick="selectDate(this, '<?php echo intval(substr($currentDate, 8, 2)); ?>日 (<?php echo ['日','月','火','水','木','金','土'][$dayOfWeek]; ?> )', '<?php echo htmlspecialchars($detailHtml, ENT_QUOTES); ?>')"
                 id="cell-<?php echo $currentDate; ?>">
                
                <span class="mobile-grid-date"><?php echo intval(substr($currentDate, 8, 2)); ?></span>
                
                <div class="mobile-grid-dots">
                    <?php if ($is_holiday): ?>
                        <div class="mobile-dot dot-holiday"></div>
                    <?php endif; ?>
                    
                    <?php if ($_SESSION['user_type'] === 'owner'): ?>
                        <?php if ($has_any_shift): 
                            $uniqueCount = isset($unique_users_by_date[$currentDate]) ? count($unique_users_by_date[$currentDate]) : 0;
                        ?>
                            <div class="mobile-staff-count"><?php echo $uniqueCount; ?></div>
                        <?php endif; ?>
                    <?php else: ?>
                        <?php if ($has_my_shift): ?>
                            <div class="mobile-dot dot-shift"></div>
                        <?php endif; ?>
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

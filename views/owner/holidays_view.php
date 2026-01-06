<?php render_header('定休日設定', true, ['calendar.css', 'dashboard.css']); ?>

<div class="container mt-4">
    <h1 class="mb-4">定休日設定</h1>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">毎週の定休日設定</h5>
        </div>
        <div class="card-body">
            <p class="card-text small text-muted mb-2">毎週定休とする曜日を選択してください（ボタン形式でタップしやすくなっています）。</p>
            <form method="post" id="holidaysForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                
                <div class="mb-3 d-flex flex-wrap gap-2 justify-content-center justify-content-md-start">
                    <?php 
                    $week_ja = ['日', '月', '火', '水', '木', '金', '土'];
                    foreach ($week_ja as $i => $day_name): 
                        $is_checked = in_array((string)$i, $current_regular_days, true) ? 'checked' : '';
                        // カラークラス: ボタンの色変え用
                        $btn_class = 'btn-outline-secondary';
                        if ($i === 0) $btn_class = 'btn-outline-danger';
                        elseif ($i === 6) $btn_class = 'btn-outline-primary';
                    ?>
                        <input type="checkbox" class="btn-check" name="regular_days[]" value="<?php echo $i; ?>" id="regular_day_<?php echo $i; ?>" autocomplete="off" <?php echo $is_checked; ?>>
                        <label class="btn <?php echo $btn_class; ?> px-4 py-2" for="regular_day_<?php echo $i; ?>"><?php echo $day_name; ?></label>
                    <?php endforeach; ?>
                </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <div class="d-flex justify-content-between align-items-center mb-2 mb-md-0">
                 <h5 class="card-title mb-0">臨時定休日の設定</h5>
            </div>
            
            <!-- Mobile Optimized Month Navigation -->
            <div class="d-flex align-items-center justify-content-between mt-2 mt-md-0">
                 <button type="button" class="btn btn-outline-secondary flex-grow-1 me-1" onclick="location.href='?month=<?php echo date('Y-m', strtotime($month . ' -1 month')); ?>'">&lt; 前月</button>
                 <input type="month" name="month" class="form-control form-control mx-1 text-center" style="max-width: 140px;" value="<?php echo htmlspecialchars($month); ?>" onchange="location.href='?month=' + this.value">
                 <button type="button" class="btn btn-outline-secondary flex-grow-1 ms-1" onclick="location.href='?month=<?php echo date('Y-m', strtotime($month . ' +1 month')); ?>'">翌月 &gt;</button>
            </div>
        </div>
        
        <div class="card-body">
            <p class="card-text small text-muted mb-3">
                カレンダーの日付をクリックして、定休日（赤色）に設定します。<br>
                <span class="badge bg-secondary opacity-50">定</span> マークは毎週定休です。
            </p>
            
            <style>
                /* ダッシュボードライクなカレンダーテーブルスタイル */
                 .calendar-table {
                    table-layout: fixed;
                    width: 100%;
                }
                .calendar-table th {
                    text-align: center;
                    padding: 10px 0;
                }
                .calendar-table td {
                    height: 100px; /* PCでの高さ */
                    vertical-align: top;
                    padding: 5px;
                    border: 1px solid #dee2e6;
                    cursor: pointer;
                    transition: background-color 0.2s;
                    position: relative;
                }
                .calendar-table td:hover {
                    background-color: #f8f9fa;
                }
                /* 選択（定休日）状態 */
                .calendar-table td.selected {
                    background-color: #ffebee !important; /* 薄い赤 */
                }
                .calendar-table td.selected .day-number {
                    color: #c62828 !important;
                    font-weight: bold;
                }
                /* 毎週定休 */
                .calendar-table td.regular-holiday {
                    background-color: #e9ecef; /* グレー */
                }

                @media (max-width: 768px) {
                    .calendar-table td {
                        height: 60px; /* モバイルでの高さ調整 */
                    }
                    .day-number {
                        font-size: 1.1rem;
                    }
                }
            </style>

            <form method="post" id="holidaysForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                
                <table class="table table-bordered calendar-table mb-4">
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
                    $firstWeekday = date('w', strtotime($start_date)); // 0 (Sun) - 6 (Sat)
                    $daysInMonth = date('t', strtotime($start_date));
                    
                    $cells = [];
                    // 空セル
                    for ($i = 0; $i < $firstWeekday; $i++) { $cells[] = null; }
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $cells[] = date('Y-m-d', strtotime("{$start_date} +".($d-1).' days'));
                    }
                    // 末尾の空セル
                    while (count($cells) % 7 !== 0) { $cells[] = null; }

                    $rowsCount = intdiv(count($cells), 7);
                    
                    for ($r = 0; $r < $rowsCount; $r++) {
                        echo "<tr>";
                        for ($c = 0; $c < 7; $c++) {
                            $idx = $r * 7 + $c;
                            $date_str = $cells[$idx];
                            
                            if ($date_str === null) {
                                echo '<td class="bg-light">&nbsp;</td>';
                                continue;
                            }
                            
                            $w = (int)date('w', strtotime($date_str));
                            $day = (int)date('j', strtotime($date_str));
                            
                            // 曜日定休かどうか
                            $is_regular_holiday = in_array((string)$w, $current_regular_days, true);
                            // 臨時定休テーブルにあるか
                            $is_in_holidays_table = in_array($date_str, $current_holidays);
                            $is_checked = $is_in_holidays_table ? 'checked' : '';
                            
                            // クラス計算
                            $td_class = '';
                            if ($is_regular_holiday) $td_class .= ' regular-holiday';
                            if ($is_in_holidays_table) $td_class .= ' selected';
                            
                            $text_class = '';
                            if ($w === 0) $text_class = 'text-danger';
                            elseif ($w === 6) $text_class = 'text-primary';

                            echo '<td class="'.$td_class.'" onclick="toggleHoliday(this)">';
                            
                            // 隠しチェックボックス
                            echo '<input type="checkbox" name="holidays[]" value="' . $date_str . '" class="d-none" ' . $is_checked . '>';
                            
                            // 日付表示
                            echo '<div class="d-flex justify-content-between align-items-start">';
                            echo '<span class="day-number ' . $text_class . '">' . $day . '</span>';
                             if ($is_regular_holiday) {
                                echo '<span class="badge bg-secondary small">定</span>';
                            }
                            echo '</div>'; // header
                            
                            // 定休日マーク（選択時表示用）
                            $display_style = $is_in_holidays_table ? '' : 'display:none;';
                            echo '<div class="holiday-mark text-center text-danger mt-1" style="'.$display_style.'"><small><i class="bi bi-x-circle-fill"></i> 休</small></div>';
                            
                            echo '</td>';
                        }
                        echo "</tr>";
                    }
                    ?>
                    </tbody>
                </table>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end fixed-bottom-action-bar-placeholder">
                    <button type="submit" class="btn btn-primary btn-lg px-5 shadow">設定を保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleHoliday(td) {
    const checkbox = td.querySelector('input[type="checkbox"]');
    const mark = td.querySelector('.holiday-mark');
    
    // トグル
    checkbox.checked = !checkbox.checked;
    
    if (checkbox.checked) {
        td.classList.add('selected');
        mark.style.display = 'block';
    } else {
        td.classList.remove('selected');
        mark.style.display = 'none';
    }
}
</script>

<?php render_footer(); ?>

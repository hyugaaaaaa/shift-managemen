<?php render_header('月間時間集計'); ?>

<div class="page-container">
    <div class="page-header">
        <h2 class="page-title">月間時間集計</h2>
        <form method="get" class="d-flex align-items-center gap-2">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-month"></i></span>
                <input type="month" name="month" value="<?php echo htmlspecialchars($selected_month); ?>" class="form-control border-start-0 ps-0" style="max-width: 200px;">
            </div>
            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm" style="height: 47.17px; font-size: 20px; display: flex; align-items: center; justify-content: center; padding-top: 0; padding-bottom: 0;">
                <i class="bi bi-search me-1"></i> 
            </button>
        </form>
    </div>

    <div class="summary-info">
        <div class="summary-item">
            <span class="summary-label">集計期間</span>
            <span class="summary-value"><?php echo htmlspecialchars($start_date); ?> ～ <?php echo htmlspecialchars($end_date); ?></span>
        </div>
        <div class="summary-item">
            <span class="summary-label">締め日</span>
            <span class="summary-value">毎月<?php echo htmlspecialchars($closing_day); ?>日</span>
        </div>
    </div>

    <div class="card-custom">
        <div class="table-responsive">
            <table class="table-custom">
                <thead>
                    <tr>
                        <th class="ps-3">名前</th>
                        <th class="text-end">時給</th>
                        <th class="text-center">出勤日数</th>
                        <th class="text-end">通常時間</th>
                        <th class="text-end">深夜時間 <small class="text-muted fw-normal">(25%UP)</small></th>
                        <th class="text-end">交通費計</th>
                        <th class="text-end">総支給額</th>
                        <th class="text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($user_stats as $stat): 
                        $u = $stat['user'];
                        $rate = (float)$u['hourly_rate'];
                        $transport = (float)$u['transportation_expense'];
                        
                        $normal_hours = round($stat['normal_minutes'] / 60, 2);
                        $night_hours = round($stat['night_minutes'] / 60, 2);
                        
                        $pay_normal = floor($stat['normal_minutes'] / 60 * $rate);
                        $pay_night = floor($stat['night_minutes'] / 60 * $rate * 1.25);
                        $pay_transport = $stat['days_worked'] * $transport;
                        
                        $total_pay = $pay_normal + $pay_night + $pay_transport;
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="user-name"><?php echo htmlspecialchars($u['username']); ?></div>
                        </td>
                        <td class="text-end amount">¥<?php echo number_format($rate); ?></td>
                        <td class="text-center"><?php echo $stat['days_worked']; ?>日</td>
                        <td class="text-end"><?php echo $normal_hours; ?>h</td>
                        <td class="text-end"><?php echo $night_hours; ?>h</td>
                        <td class="text-end amount">¥<?php echo number_format($pay_transport); ?></td>
                        <td class="text-end total-amount">¥<?php echo number_format($total_pay); ?></td>
                        <td class="text-center">
                            <a href="<?php echo BASE_PATH; ?>/parttime/payslip_view.php?month=<?php echo htmlspecialchars($selected_month); ?>&user_id=<?php echo htmlspecialchars($u['user_id']); ?>" target="_blank" class="btn btn-outline-primary btn-view">
                                明細
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php render_footer(); ?>

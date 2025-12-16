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
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-calendar3 me-2"></i><?php echo htmlspecialchars("{$year}年{$month}月の勤務時間集計"); ?>
            </div>
            <div class="card-body">
                <div class="table-responsive-mobile">
                    <table class="table table-hover table-bordered align-middle">
                        <!-- Table content would go here if this were a new table -->
                    </table>
                </div>
            </div>
        </div>
        <div class="summary-item">
            <span class="summary-label">締め日</span>
            <span class="summary-value">毎月<?php echo htmlspecialchars($closing_day); ?>日</span>
        </div>
    </div>

    <div class="card-custom">
    <div class="card-custom">
        <!-- Desktop View: Table -->
        <div class="d-none d-lg-block table-responsive-mobile">
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

        <!-- Mobile View: Cards -->
        <div class="d-lg-none">
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
            <div class="card mb-3 shadow-sm">
                <div class="card-header bg-primary bg-opacity-10 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-dark"><i class="bi bi-person-circle me-2"></i><?php echo htmlspecialchars($u['username']); ?></h6>
                    <span class="badge bg-primary"><?php echo $stat['days_worked']; ?>日出勤</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <div class="monthly-stat-item">
                                <div class="stat-label"><i class="bi bi-currency-yen me-1"></i>時給</div>
                                <div class="stat-value">¥<?php echo number_format($rate); ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="monthly-stat-item">
                                <div class="stat-label"><i class="bi bi-bus-front me-1"></i>交通費計</div>
                                <div class="stat-value">¥<?php echo number_format($pay_transport); ?></div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="monthly-stat-item">
                                <div class="stat-label"><i class="bi bi-sun me-1"></i>通常時間</div>
                                <div class="stat-value"><?php echo $normal_hours; ?>h</div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="monthly-stat-item">
                                <div class="stat-label"><i class="bi bi-moon-stars me-1"></i>深夜時間</div>
                                <div class="stat-value"><?php echo $night_hours; ?>h</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="total-pay-mobile">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold">総支給額</span>
                            <span class="fs-4 fw-bold text-primary">¥<?php echo number_format($total_pay); ?></span>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="<?php echo BASE_PATH; ?>/parttime/payslip_view.php?month=<?php echo htmlspecialchars($selected_month); ?>&user_id=<?php echo htmlspecialchars($u['user_id']); ?>" target="_blank" class="btn btn-outline-primary w-100">
                            <i class="bi bi-file-earmark-text me-1"></i>給与明細を表示
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php render_footer(); ?>

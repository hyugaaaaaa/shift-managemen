<?php render_header('月間時間集計', true, ['monthly_hours.css', 'dashboard.css']); ?>

<div class="page-container">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="monthly-title mb-0">月間時間集計</h2>
        <form method="get" class="d-flex align-items-center gap-2">
            <div class="input-group" style="max-width: 200px;">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar-month"></i></span>
                <input type="month" name="month" value="<?php echo htmlspecialchars($selected_month); ?>" class="form-control border-start-0 ps-0">
            </div>
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>

    <?php if(!empty($msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>
    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white">
            <i class="bi bi-calendar3 me-2"></i><?php echo htmlspecialchars("{$year}年{$month}月の勤務時間集計"); ?>
        </div>
        <div class="card-body">
            <div class="d-flex gap-4 flex-wrap mb-0">
                <div>
                    <small class="text-muted d-block">集計期間</small>
                    <span class="fw-bold"><?php echo htmlspecialchars($start_date); ?> ～ <?php echo htmlspecialchars($end_date); ?></span>
                </div>
                <div>
                    <small class="text-muted d-block">締め日</small>
                    <span class="fw-bold">毎月<?php echo htmlspecialchars($closing_day); ?>日</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Desktop View: Table -->
    <div class="d-none d-lg-block">
        <div class="card shadow-sm">
            <div class="card-body p-0">
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
    </div>

    <!-- Mobile View: Cards (従業員管理スタイル) -->
    <div class="d-lg-none">
        <?php if (empty($user_stats)): ?>
            <p class="text-center text-muted py-3">データがありません。</p>
        <?php else: ?>
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
            <div class="card mb-3 shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h5 class="card-title fw-bold mb-0">
                            <i class="bi bi-person-circle text-primary me-2"></i><?php echo htmlspecialchars($u['username']); ?>
                        </h5>
                        <span class="badge bg-light text-dark border"><?php echo $stat['days_worked']; ?>日出勤</span>
                    </div>
                    <hr class="my-2">
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">時給</small>
                            <span class="fw-bold">¥<?php echo number_format($rate); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">交通費計</small>
                            <span class="fw-bold">¥<?php echo number_format($pay_transport); ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block"><i class="bi bi-sun me-1"></i>通常時間</small>
                            <span class="fw-bold"><?php echo $normal_hours; ?>h</span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block"><i class="bi bi-moon-stars me-1"></i>深夜時間</small>
                            <span class="fw-bold"><?php echo $night_hours; ?>h</span>
                        </div>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted d-block">総支給額</small>
                            <span class="fs-5 fw-bold text-primary">¥<?php echo number_format($total_pay); ?></span>
                        </div>
                        <a href="<?php echo BASE_PATH; ?>/parttime/payslip_view.php?month=<?php echo htmlspecialchars($selected_month); ?>&user_id=<?php echo htmlspecialchars($u['user_id']); ?>" target="_blank" class="btn btn-primary btn-sm">
                            <i class="bi bi-file-earmark-text me-1"></i>明細
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php render_footer(); ?>

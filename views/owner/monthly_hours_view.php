<?php render_header('月間時間集計'); ?>
<div class="row">
  <div class="col-md-12">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2>月間時間集計</h2>
      <form method="get" class="d-flex align-items-center">
        <input type="month" name="month" class="form-control me-2" value="<?php echo htmlspecialchars($selected_month); ?>">
        <button type="submit" class="btn btn-primary">表示</button>
      </form>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>集計期間:</strong> <?php echo $start_date; ?> ～ <?php echo $end_date; ?>
                </div>
                <div class="col-md-6 text-end">
                    <strong>締め日:</strong> 毎月<?php echo $closing_day; ?>日
                </div>
            </div>
        </div>
    </div>

    <div class="card">
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>名前</th>
              <th>時給</th>
              <th>出勤日数</th>
              <th>通常時間</th>
              <th>深夜時間<br><small>(25%UP)</small></th>
              <th>交通費計</th>
              <th>総支給額</th>
              <th>明細</th>
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
              <td><?php echo htmlspecialchars($u['username']); ?></td>
              <td>¥<?php echo number_format($rate); ?></td>
              <td><?php echo $stat['days_worked']; ?>日</td>
              <td><?php echo $normal_hours; ?>h</td>
              <td><?php echo $night_hours; ?>h</td>
              <td>¥<?php echo number_format($pay_transport); ?></td>
              <td class="fw-bold">¥<?php echo number_format($total_pay); ?></td>
              <td>
                <a href="<?php echo BASE_PATH; ?>/parttime/payslip_view.php?month=<?php echo $selected_month; ?>&user_id=<?php echo $u['user_id']; ?>" target="_blank" class="btn btn-sm btn-outline-secondary">表示</a>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<?php render_footer(); ?>

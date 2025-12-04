<?php
/**
 * 希望シフト提出画面のビュー
 * 
 * ユーザーが希望するシフトの日付、開始・終了時刻を入力するフォームを表示します。
 * エラーメッセージや成功メッセージがある場合はそれらも表示します。
 */
render_header('希望シフト提出'); ?>
<div class="row">
  <div class="col-md-8">
    <h2>希望シフト提出</h2>
    
    <?php if(!empty($deadline_msg)): ?>
        <div class="alert alert-info">
            <strong><?php echo htmlspecialchars($target_year_month); ?>分</strong>のシフト提出を受け付けています。<br>
            <?php echo htmlspecialchars($deadline_msg); ?>
            <?php if(!empty($holiday_msg)): ?>
                <br><strong class="text-danger"><?php echo htmlspecialchars($holiday_msg); ?></strong>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if(!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    
    <?php if (empty($is_past_deadline) || !$is_past_deadline): ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
      <div class="mb-3">
        <label class="form-label">シフトパターンから選択</label>
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
        <label class="form-label">日付</label>
        <input type="date" name="shift_date" class="form-control" value="<?php echo htmlspecialchars($shift_date); ?>" min="<?php echo htmlspecialchars($min_date); ?>" max="<?php echo htmlspecialchars($max_date); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">開始時刻</label>
        <input type="time" name="start_time" class="form-control" value="<?php echo htmlspecialchars($start_time); ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">終了時刻</label>
        <input type="time" name="end_time" class="form-control" value="<?php echo htmlspecialchars($end_time); ?>" required>
      </div>
      <button class="btn btn-primary" type="submit">提出</button>
    </form>

    <script>
    document.getElementById('shift_template').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const start = selectedOption.getAttribute('data-start');
        const end = selectedOption.getAttribute('data-end');
        
        if (start && end) {
            document.getElementsByName('start_time')[0].value = start;
            document.getElementsByName('end_time')[0].value = end;
        }
    });
    </script>

    <?php endif; ?>
  </div>
</div>
<?php render_footer(); ?>

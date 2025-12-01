<?php render_header('希望シフト提出'); ?>
<div class="row">
  <div class="col-md-8">
    <h2>希望シフト提出</h2>
    <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <?php if(!empty($success)): ?><div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
      <div class="mb-3">
        <label class="form-label">日付</label>
        <input type="date" name="shift_date" class="form-control" value="<?php echo htmlspecialchars($shift_date); ?>" required>
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
  </div>
</div>
<?php render_footer(); ?>

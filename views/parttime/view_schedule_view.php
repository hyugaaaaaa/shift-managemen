<?php
/**
 * 確定シフト一覧画面のビュー
 * 
 * 確定したシフトの一覧を表形式で表示します。
 * 日跨ぎシフトの場合は、分割された状態で表示されます。
 */
render_header('確定シフト'); ?>
<div class="row justify-content-center">
  <div class="col-12 col-md-8">
    <div class="text-center mb-3"><h1 class="h4">確定シフト</h1></div>
    <?php if(empty($display_schedules)): ?>
      <div class="alert alert-info text-center">確定されたシフトはまだありません。</div>
    <?php else: ?>
      <div class="table-responsive">
      <table class="table table-sm shift-table mx-auto">
        <thead>
          <tr>
            <th>日付</th>
            <th>開始</th>
            <th>終了</th>
            <th>備考</th>
            <th>登録日時</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach($display_schedules as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['shift_date']); ?></td>
            <td><?php echo htmlspecialchars($row['display_start']); ?></td>
            <td><?php echo htmlspecialchars($row['display_end']); ?></td>
            <td><?php echo htmlspecialchars($row['note'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
    <div class="text-center mt-3">
      <a class="btn btn-secondary" href="<?php echo BASE_PATH; ?>/parttime/submit_shift.php">希望提出に戻る</a>
    </div>
  </div>
</div>
<?php render_footer(); ?>

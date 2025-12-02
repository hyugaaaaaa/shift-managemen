<?php render_header('システム設定'); ?>

<div class="container mt-4">
    <h1 class="mb-4">システム設定</h1>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <h5 class="card-title mb-0">シフト提出設定</h5>
        </div>
        <div class="card-body">
            <form method="post" action="system_settings.php">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                
                <div class="mb-3">
                    <label for="shift_submission_deadline_day" class="form-label">毎月のシフト提出締め切り日</label>
                    <div class="input-group">
                        <span class="input-group-text">毎月</span>
                        <input type="number" class="form-control" id="shift_submission_deadline_day" name="shift_submission_deadline_day" value="<?php echo htmlspecialchars($current_deadline); ?>" min="1" max="31" required>
                        <span class="input-group-text">日</span>
                    </div>
                    <div class="form-text">この日付を過ぎると、アルバイト従業員は翌月のシフト希望を提出できなくなります。</div>
                </div>

                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
    </div>
</div>

<?php render_footer(); ?>

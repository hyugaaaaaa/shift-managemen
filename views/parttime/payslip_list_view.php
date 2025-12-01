<?php render_header('給与明細'); ?>
<div class="row justify-content-center">
  <div class="col-md-8">
    <h1 class="h3 mb-4">給与明細</h1>
    
    <?php if ($msg): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <?php if (empty($user['payslip_consent'])): ?>
        <div class="card border-warning mb-4">
            <div class="card-header bg-warning text-dark">
                <strong>給与明細の電子交付に関する同意</strong>
            </div>
            <div class="card-body">
                <p>給与明細をWeb上で閲覧・印刷（電子交付）するためには、事前の同意が必要です。</p>
                <p>同意いただけない場合は、管理者までお申し出ください（書面での交付となります）。</p>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="consent" value="1" id="consentCheck" required>
                        <label class="form-check-label" for="consentCheck">
                            給与明細を電子交付（Web閲覧・印刷）で受け取ることに同意します。
                        </label>
                    </div>
                    <button type="submit" class="btn btn-primary">同意して利用開始</button>
                </form>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-info">
            <small>あなたは <?php echo htmlspecialchars($user['payslip_consent_date']); ?> に電子交付に同意しています。</small>
        </div>

        <div class="list-group">
            <?php foreach ($months as $m): ?>
                <a href="payslip_view.php?month=<?php echo $m; ?>" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" target="_blank">
                    <?php echo date('Y年n月分', strtotime($m . '-01')); ?>
                    <span class="badge bg-secondary rounded-pill">明細を表示</span>
                </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </div>
</div>
<?php render_footer(); ?>

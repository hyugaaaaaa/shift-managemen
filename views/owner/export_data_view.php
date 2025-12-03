<?php render_header('データ出力'); ?>

<div class="container mt-4">
    <h1 class="mb-4">データ出力</h1>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if(!empty($msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">給与明細データ (CSV)</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">指定した月の全従業員の給与計算結果をCSV形式でC:\shift_managementに保存します。</p>
                    <form method="post" action="export_data.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                        <input type="hidden" name="export_type" value="payslip">
                        
                        <div class="mb-3">
                            <label for="month_payslip" class="form-label">対象月</label>
                            <input type="month" class="form-control" id="month_payslip" name="month" value="<?php echo date('Y-m'); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> サーバーに保存</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">シフト表データ (CSV)</h5>
                </div>
                <div class="card-body">
                    <p class="card-text">指定した月の確定シフトデータをCSV形式でC:\shift_managementに保存します。</p>
                    <form method="post" action="export_data.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                        <input type="hidden" name="export_type" value="shifts">
                        
                        <div class="mb-3">
                            <label for="month_shifts" class="form-label">対象月</label>
                            <input type="month" class="form-control" id="month_shifts" name="month" value="<?php echo date('Y-m'); ?>" required>
                        </div>
                        
                        <button type="submit" class="btn btn-success"><i class="bi bi-save"></i> サーバーに保存</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

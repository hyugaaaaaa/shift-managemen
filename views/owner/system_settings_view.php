<?php render_header('システム設定'); ?>

<div class="container mt-4">
    <h1 class="mb-4">システム設定</h1>

    <?php if(!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <?php if(!empty($msg)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
    <?php endif; ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="card-title mb-0"><i class="bi bi-building"></i> 企業情報 / 招待コード</h5>
        </div>
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <p class="mb-1"><strong>現在の招待コード:</strong></p>
                    <div class="display-6 monospace fw-bold text-dark bg-light border p-2 rounded d-inline-block">
                        <?php echo htmlspecialchars($company_code ?? '---'); ?>
                    </div>
                    <p class="text-muted small mt-2">
                        このコードをスタッフに共有してください。スタッフはこのコードを使って登録できます。
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    <form method="post" action="regenerate_code.php" onsubmit="return confirm('招待コードを再発行しますか？\n\n・古いコードは即座に無効になります。\n・既にこのコードを持っているスタッフに新しいコードを伝え直す必要があります。\n・既存のスタッフ登録には影響しません。');">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                        <button type="submit" class="btn btn-outline-danger btn-sm">
                            <i class="bi bi-arrow-repeat"></i> 招待コードを再発行する
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

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

                <hr class="my-4">
                <h5 class="mb-3">給与設定</h5>

                <div class="mb-3">
                    <label for="closing_day" class="form-label">給与締め日</label>
                    <div class="input-group">
                        <span class="input-group-text">毎月</span>
                        <input type="number" class="form-control" id="closing_day" name="closing_day" value="<?php echo htmlspecialchars($current_closing_day); ?>" min="1" max="31" required>
                        <span class="input-group-text">日</span>
                    </div>
                    <div class="form-text">給与計算の締め日を設定します（例: 31 = 末日締め）。</div>
                </div>

                <div class="mb-3">
                    <label for="payment_day" class="form-label">給与支払日</label>
                    <div class="input-group">
                        <span class="input-group-text">毎月</span>
                        <input type="number" class="form-control" id="payment_day" name="payment_day" value="<?php echo htmlspecialchars($current_payment_day); ?>" min="1" max="31" required>
                        <span class="input-group-text">日</span>
                    </div>
                    <div class="form-text">給与の支払日を設定します。</div>
                </div>



                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
    </div>
</div>

<?php render_footer(); ?>

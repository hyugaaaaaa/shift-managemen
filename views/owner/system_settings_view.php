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

                <hr class="my-4">
                <h5 class="mb-3">LINE Messaging API設定</h5>
                <div class="mb-3">
                    <label for="line_channel_access_token" class="form-label">チャンネルアクセストークン (長期)</label>
                    <textarea class="form-control" id="line_channel_access_token" name="line_channel_access_token" rows="3"><?php echo htmlspecialchars($current_line_token); ?></textarea>
                    <div class="form-text">
                        LINE Developersコンソールで発行した「チャンネルアクセストークン（長期）」を入力してください。<br>
                        これが設定されていないと、お知らせのLINE通知は送信されません。
                    </div>
                </div>

                <div class="mb-3">
                    <label for="line_channel_secret" class="form-label">チャンネルシークレット</label>
                    <input type="text" class="form-control" id="line_channel_secret" name="line_channel_secret" value="<?php echo htmlspecialchars($current_line_secret); ?>">
                    <div class="form-text">
                        LINE Developersコンソールで確認できる「チャンネルシークレット」を入力してください。<br>
                        Webhookの署名検証に使用します。
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">保存</button>
            </form>
        </div>
    </div>
</div>

<?php render_footer(); ?>

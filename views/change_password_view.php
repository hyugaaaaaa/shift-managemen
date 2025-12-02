<?php render_header('パスワード変更'); ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">パスワード変更</h5>
            </div>
            <div class="card-body">
                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if(!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
                <?php endif; ?>

                <form method="post" action="change_password.php">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    
                    <div class="mb-3">
                        <label for="current_password" class="form-label">現在のパスワード</label>
                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                    </div>

                    <div class="mb-3">
                        <label for="new_password" class="form-label">新しいパスワード (8文字以上)</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">新しいパスワード (確認)</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8">
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">変更する</button>
                        <a href="dashboard.php" class="btn btn-outline-secondary">ダッシュボードに戻る</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

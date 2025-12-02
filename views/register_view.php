<?php render_header('新規登録'); ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">初期アカウント登録</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    システムにユーザーが登録されていません。<br>
                    最初のユーザー（管理者）を登録してください。
                </div>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form method="post" action="register.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    
                    <div class="mb-3">
                        <label for="username" class="form-label">ユーザー名</label>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">パスワード (8文字以上)</label>
                        <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">パスワード (確認)</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">登録して開始</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

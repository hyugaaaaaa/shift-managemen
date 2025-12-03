<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                ユーザー設定
            </div>
            <div class="card-body">
                <h5 class="card-title mb-4">LINE通知連携設定</h5>
                
                <?php if ($msg): ?>
                    <div class="alert alert-success"><?php echo h($msg); ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <p>
                    LINE連携を行うことで、お知らせやシフト確定の通知をLINEで受け取ることができます。<br>
                    公式アカウントを友だち追加し、あなたのLINE User IDを入力してください。
                </p>

                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    
                    <div class="mb-3">
                        <label for="line_user_id" class="form-label">LINE User ID</label>
                        <input type="text" class="form-control" id="line_user_id" name="line_user_id" 
                               value="<?php echo h($user['line_user_id'] ?? ''); ?>" 
                               placeholder="Uから始まるIDを入力してください">
                        <div class="form-text">
                            ※IDを削除して保存すると、連携が解除されます。
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">設定を保存</button>
                </form>
            </div>
        </div>
        
        <div class="mt-3 text-center">
            <a href="<?php echo BASE_PATH; ?>/change_password.php" class="btn btn-outline-secondary">パスワード変更はこちら</a>
        </div>
    </div>
</div>

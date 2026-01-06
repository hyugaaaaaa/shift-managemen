<?php render_header('アルバイト登録', true, ['auth.css']); ?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0">アルバイト・スタッフ新規登録</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">
                    店舗・企業から共有された<strong>招待コード</strong>を入力して登録してください。
                </p>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <form method="post" action="register_staff.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    
                    <div class="mb-4 p-3 bg-light border rounded">
                        <label for="company_code" class="form-label fw-bold">招待コード <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg text-uppercase monospace" id="company_code" name="company_code" required placeholder="例: A1B2C3D4" value="<?php echo h($company_code ?? ''); ?>">
                        <div class="form-text">オーナーから伝えられた8桁のコードを入力してください。</div>
                    </div>

                    <div class="mb-3">
                        <label for="username" class="form-label">ユーザー名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="username" value="<?php echo h($username ?? ''); ?>">
                        <div class="form-text">シフト表などに表示される名前です。</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email" value="<?php echo h($email ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">パスワード <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label">パスワード（確認） <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required minlength="8" autocomplete="new-password">
                        </div>
                    </div>

                    <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="agree_terms" name="agree_terms" required <?php echo isset($agree_terms) && $agree_terms ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="agree_terms">
                            <a href="terms_of_service.php" target="_blank">利用規約</a> および <a href="privacy_policy.php" target="_blank">プライバシーポリシー</a> に同意します <span class="text-danger">*</span>
                        </label>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-success btn-lg">登録して開始</button>
                        <a href="index.php" class="btn btn-outline-secondary">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

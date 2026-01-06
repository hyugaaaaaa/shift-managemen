<?php render_header('企業アカウント登録', true, ['auth.css']); ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">企業アカウント新規登録</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small">オーナー様向けのアカウント作成ページです。</p>

                <?php if(!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo h($error); ?></div>
                <?php endif; ?>

                <form method="post" action="register_company.php" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    
                    <h6 class="border-bottom pb-2 mb-3">企業情報</h6>

                    <div class="mb-3">
                        <label for="company_name" class="form-label">企業名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="company_name" name="company_name" required value="<?php echo h($company_name ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="representative_name" class="form-label">代表者名</label>
                            <input type="text" class="form-control" id="representative_name" name="representative_name" value="<?php echo h($representative_name ?? ''); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="phone_number" class="form-label">電話番号</label>
                            <input type="text" class="form-control" id="phone_number" name="phone_number" value="<?php echo h($phone_number ?? ''); ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="address" class="form-label">住所</label>
                        <input type="text" class="form-control" id="address" name="address" value="<?php echo h($address ?? ''); ?>">
                    </div>

                    <h6 class="border-bottom pb-2 mb-3 mt-4">オーナー情報</h6>

                    <div class="mb-3">
                        <label for="username" class="form-label">ユーザー名 <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="username" name="username" required autocomplete="username" value="<?php echo h($username ?? ''); ?>">
                        <div class="form-text">ログイン時に使用します。</div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">メールアドレス <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required autocomplete="email" value="<?php echo h($email ?? ''); ?>">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">パスワード <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password" name="password" required minlength="8" autocomplete="new-password">
                            <div class="form-text">8文字以上で設定してください。</div>
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
                        <button type="submit" class="btn btn-primary btn-lg">登録して開始</button>
                        <a href="index.php" class="btn btn-outline-secondary">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

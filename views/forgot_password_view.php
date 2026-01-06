<?php render_header('パスワード再設定申請', false, ['auth.css']); ?>
<div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-body">
                        <h3 class="text-center mb-4">パスワード再設定</h3>
                        
                        <?php if (!empty($success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                            </div>
                            <div class="text-center">
                                <a href="index.php" class="btn btn-link">ログイン画面に戻る</a>
                            </div>
                        <?php else: ?>
                            <?php if (!empty($error)): ?>
                                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                            <?php endif; ?>

                            <?php if (!empty($companies)): ?>
                                <p class="text-muted mb-4">このメールアドレスは複数の企業に登録されています。<br>パスワードを再設定したい企業を選択してください。</p>
                                <div class="list-group mb-4">
                                    <?php foreach ($companies as $comp): ?>
                                        <form method="post" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                                            <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($comp['company_id']); ?>">
                                            <div>
                                                <div class="fw-bold"><?php echo htmlspecialchars($comp['company_name']); ?></div>
                                                <small class="text-muted">ユーザー名: <?php echo htmlspecialchars($comp['username']); ?></small>
                                            </div>
                                            <button type="submit" class="btn btn-sm btn-outline-primary">選択</button>
                                        </form>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-4">登録しているメールアドレスを入力してください。再設定用のリンクを送信します。</p>

                                <form method="post">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">メールアドレス</label>
                                        <input type="email" class="form-control" id="email" name="email" required value="<?php echo htmlspecialchars($email ?? ''); ?>">
                                    </div>
                                    <div class="d-grid gap-2">
                                        <button type="submit" class="btn btn-primary">送信</button>
                                    </div>
                                </form>
                            <?php endif; ?>
                            
                            <div class="text-center mt-3">
                                <a href="index.php" class="text-decoration-none">ログイン画面に戻る</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php render_footer(); ?>

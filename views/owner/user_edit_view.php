<?php render_header('従業員登録・編集'); ?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <?php echo $id ? '従業員編集' : '従業員新規登録'; ?>
            </div>
            <div class="card-body">
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <?php if (!empty($msg)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
                <?php endif; ?>
                
                <form method="post" autocomplete="off">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">ユーザー名</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">時給 (円)</label>
                        <input type="number" name="hourly_rate" class="form-control" value="<?php echo htmlspecialchars($user['hourly_rate'] ?? '1000'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">交通費 (円/日)</label>
                        <input type="number" name="transportation_expense" class="form-control" value="<?php echo htmlspecialchars($user['transportation_expense'] ?? '0'); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">パスワード <?php if($id) echo '<small class="text-muted">（変更する場合のみ入力）</small>'; ?></label>
                        <div class="input-group">
                            <input type="password" name="password" id="password" class="form-control" <?php if(!$id) echo 'required'; ?> autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye"></i> 表示
                            </button>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">パスワード（確認）</label>
                        <div class="input-group">
                            <input type="password" name="password_confirm" id="password_confirm" class="form-control" <?php if(!$id) echo 'required'; ?> autocomplete="new-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePasswordConfirm">
                                <i class="bi bi-eye"></i> 表示
                            </button>
                        </div>
                    </div>

                    <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        function setupToggle(inputId, buttonId) {
                            const input = document.getElementById(inputId);
                            const button = document.getElementById(buttonId);
                            
                            button.addEventListener('click', function() {
                                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                                input.setAttribute('type', type);
                                button.innerHTML = type === 'password' ? '<i class="bi bi-eye"></i> 表示' : '<i class="bi bi-eye-slash"></i> 非表示';
                            });
                        }

                        setupToggle('password', 'togglePassword');
                        setupToggle('password_confirm', 'togglePasswordConfirm');
                    });
                    </script>
                    
                    <div class="d-flex justify-content-between">
                        <a href="users.php" class="btn btn-secondary">戻る</a>
                        <button type="submit" class="btn btn-primary"><?php echo $id ? '更新' : '登録'; ?></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>

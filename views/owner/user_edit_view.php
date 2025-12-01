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
                
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <div class="mb-3">
                        <label class="form-label">ユーザー名</label>
                        <input type="text" name="username" class="form-control" value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>" required>
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
                        <input type="password" name="password" class="form-control" <?php if(!$id) echo 'required'; ?>>
                    </div>
                    
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

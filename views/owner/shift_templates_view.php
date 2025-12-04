<?php render_header('シフトパターン管理'); ?>
<div class="row">
    <div class="col-md-12">
        <h1 class="h4 mb-3">シフトパターン管理</h1>
        
        <?php if(!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card mb-4">
            <div class="card-header">新規パターン登録</div>
            <div class="card-body">
                <form method="post" class="row g-3 align-items-end">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                    <input type="hidden" name="action" value="add">
                    
                    <div class="col-md-4">
                        <label class="form-label">パターン名 <small class="text-muted">(例: 早番A)</small></label>
                        <input type="text" name="template_name" class="form-control" required placeholder="早番">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">開始時間</label>
                        <input type="time" name="start_time" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">終了時間</label>
                        <input type="time" name="end_time" class="form-control" required>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">追加</button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">登録済みパターン</div>
            <div class="card-body">
                <?php if(empty($templates)): ?>
                    <p class="text-muted mb-0">登録されたパターンはありません。</p>
                <?php else: ?>
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>パターン名</th>
                                <th>時間</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($templates as $tpl): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($tpl['template_name']); ?></td>
                                <td>
                                    <?php echo htmlspecialchars(substr($tpl['start_time'], 0, 5)); ?> - 
                                    <?php echo htmlspecialchars(substr($tpl['end_time'], 0, 5)); ?>
                                </td>
                                <td>
                                    <form method="post" style="display:inline" onsubmit="return confirm('本当に削除しますか？');">
                                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="template_id" value="<?php echo $tpl['template_id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>

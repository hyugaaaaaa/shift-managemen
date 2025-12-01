<?php render_header('従業員管理'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>従業員管理</h2>
            <a href="user_edit.php" class="btn btn-primary">新規登録</a>
        </div>
        
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>名前</th>
                            <th>時給</th>
                            <th>交通費(日)</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>¥<?php echo number_format($user['hourly_rate']); ?></td>
                            <td>¥<?php echo number_format($user['transportation_expense']); ?></td>
                            <td>
                                <a href="user_edit.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-outline-primary">編集</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php render_footer(); ?>

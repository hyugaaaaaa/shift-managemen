<?php render_header('従業員管理'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>従業員管理</h2>
            <a href="user_edit.php" class="btn btn-primary">新規登録</a>
        </div>
        
        <?php if(!empty($msg)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
        <?php endif; ?>
        <?php if(!empty($error)): ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
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
                                <form method="post" style="display:inline-block;" id="form-delete-<?php echo $user['user_id']; ?>">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars(generate_csrf_token()); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="user_id" value="<?php echo $user['user_id']; ?>">
                                    <button type="button" class="btn btn-sm btn-outline-danger" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#deleteModal" 
                                            data-user-id="<?php echo $user['user_id']; ?>" 
                                            data-username="<?php echo htmlspecialchars($user['username']); ?>">
                                        削除
                                    </button>
                                </form>
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

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteModalLabel">ユーザー削除の確認</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p>以下のユーザーを削除しますか？</p>
        <p class="fw-bold text-danger" id="deleteUserName"></p>
        <p class="small text-muted">※この操作は取り消せません。関連するシフトや給与データも全て削除（または非表示）されます。</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteBtn">削除する</button>
        
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var deleteModal = document.getElementById('deleteModal');
    var confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
    var targetFormId = null;

    deleteModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var userId = button.getAttribute('data-user-id');
        var username = button.getAttribute('data-username');
        
        var modalUserName = deleteModal.querySelector('#deleteUserName');
        modalUserName.textContent = username;
        
        targetFormId = 'form-delete-' + userId;
    });

    confirmDeleteBtn.addEventListener('click', function() {
        if (targetFormId) {
            document.getElementById(targetFormId).submit();
        }
    });
});
</script>
<?php render_footer(); ?>

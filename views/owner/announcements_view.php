<div class="row">
    <div class="col-md-12">
        <h1 class="h4 mb-4">お知らせ管理</h1>

        <?php if ($msg): ?>
            <div class="alert alert-success"><?php echo h($msg); ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo h($error); ?></div>
        <?php endif; ?>

        <!-- 新規作成フォーム -->
        <div class="card mb-4">
            <div class="card-header">
                新規お知らせ作成
            </div>
            <div class="card-body">
                <form method="post" id="announcementForm">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    <input type="hidden" name="action" value="create">
                    
                    <div class="mb-3">
                        <label class="form-label">テンプレート選択</label>
                        <select class="form-select" id="templateSelect">
                            <option value="">-- テンプレートを選択してください --</option>
                            <option value="request">シフト提出のお願い</option>
                            <option value="confirmed">シフト確定の連絡</option>
                            <option value="urgent">緊急連絡</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">送信対象</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="target" id="targetAll" value="all" checked>
                                <label class="form-check-label" for="targetAll">全員</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="target" id="targetUnsubmitted" value="unsubmitted">
                                <label class="form-check-label" for="targetUnsubmitted">シフト未提出者のみ</label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3" id="monthSelector" style="display:none;">
                        <label for="target_month" class="form-label">対象月</label>
                        <input type="month" class="form-control" id="target_month" name="target_month" value="<?php echo date('Y-m', strtotime('+1 month')); ?>">
                        <div class="form-text">指定した月にシフト希望を出していない従業員に送信します。</div>
                    </div>

                    <div class="mb-3">
                        <label for="title" class="form-label">タイトル</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">本文</label>
                        <textarea class="form-control" id="content" name="content" rows="6" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">作成して通知</button>
                </form>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const templates = {
                        'request': {
                            title: '○月分シフト提出のお願い',
                            content: 'お疲れ様です。\n来月分のシフト希望の提出期限が近づいています。\n\n【提出期限】\n○月○日（○）まで\n\nダッシュボード画面より、期間内の希望シフトの提出をお願いします。\n期限を過ぎると希望通りのシフトが組めない場合がありますので、早めの提出にご協力をお願いします。'
                        },
                        'confirmed': {
                            title: '【重要】○月のシフトが確定しました',
                            content: 'お疲れ様です。\nシフト調整が完了し、来月のシフトが確定しました。\n\n各自、システムにログインしてダッシュボードより自分のシフトを確認してください。\nもし予定と異なる場合や変更が必要な場合は、至急店長まで連絡してください。\n\nよろしくお願いいたします。'
                        },
                        'urgent': {
                            title: '【業務連絡】',
                            content: 'お疲れ様です。\n\n（ここに本文を入力）\n\n確認をお願いします。'
                        }
                    };

                    // テンプレート適用
                    document.getElementById('templateSelect').addEventListener('change', function() {
                        const key = this.value;
                        if (templates[key]) {
                            document.getElementById('title').value = templates[key].title;
                            document.getElementById('content').value = templates[key].content;
                        }
                    });

                    // 送信対象切り替え
                    const targetRadios = document.getElementsByName('target');
                    const monthSelector = document.getElementById('monthSelector');
                    
                    function toggleMonthSelector() {
                        let isUnsubmitted = false;
                        for (const radio of targetRadios) {
                            if (radio.checked && radio.value === 'unsubmitted') {
                                isUnsubmitted = true;
                                break;
                            }
                        }
                        monthSelector.style.display = isUnsubmitted ? 'block' : 'none';
                        const monthInput = document.getElementById('target_month');
                        if (isUnsubmitted) {
                            monthInput.setAttribute('required', 'required');
                        } else {
                            monthInput.removeAttribute('required');
                        }
                    }

                    for (const radio of targetRadios) {
                        radio.addEventListener('change', toggleMonthSelector);
                    }
                    
                    // 初期状態適用
                    toggleMonthSelector();
                });
                </script>
            </div>
        </div>

        <!-- お知らせ一覧 -->
        <!-- お知らせ一覧 -->
        <?php if (empty($announcements)): ?>
            <div class="card">
                <div class="card-header">過去のお知らせ</div>
                <div class="card-body">
                    <p class="text-muted">お知らせはありません。</p>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>過去のお知らせ</span>
                    <button type="button" class="btn btn-danger btn-sm" id="bulkDeleteBtn" disabled>
                        <i class="bi bi-trash"></i> 選択した項目を削除
                    </button>
                </div>
                <div class="card-body">
                    <form method="post" id="bulkDeleteForm">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                        <input type="hidden" name="action" value="delete_multiple">
                        
                        <div class="mb-2 ms-2">
                             <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="selectAll">
                                <label class="form-check-label" for="selectAll">全て選択</label>
                            </div>
                        </div>

                        <div class="list-group">
                            <?php foreach ($announcements as $a): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 align-items-start">
                                    <div class="me-3 mt-1">
                                        <input class="form-check-input delete-checkbox" type="checkbox" name="ids[]" value="<?php echo $a['id']; ?>">
                                    </div>
                                    <div style="flex: 1;">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h5 class="mb-1"><?php echo h($a['title']); ?></h5>
                                            <small class="text-muted"><?php echo h($a['created_at']); ?></small>
                                        </div>
                                        <p class="mb-1" style="white-space: pre-wrap;"><?php echo h($a['content']); ?></p>
                                    </div>
                                    <div class="ms-3">
                                        <button type="button" class="btn btn-outline-danger btn-sm" onclick="confirmDelete(<?php echo $a['id']; ?>, '<?php echo h($a['title']); ?>')">
                                            <i class="bi bi-trash"></i> 削除
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        </div>
                    </form>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- 削除確認モーダル (ループ外に1つだけ配置) -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">お知らせの削除</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteConfirmMsg">以下のお知らせを削除してもよろしいですか？</p>
                <p class="fw-bold" id="deleteTargetTitle"></p>
                <p class="text-danger small">この操作は取り消せません。</p>
            </div>
            <div class="modal-footer">
                <!-- 個別削除用フォーム -->
                <form method="post" id="singleDeleteForm" style="display:none;">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="deleteTargetId">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="submit" class="btn btn-danger">削除する</button>
                </form>

                <!-- 一括削除用ボタン (JavaScriptで元のフォームを送信) -->
                <div id="bulkDeleteActions" style="display:none;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                    <button type="button" class="btn btn-danger" id="confirmBulkDeleteBtn">削除する</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- 一括削除関連 ---
    const selectAllCheckbox = document.getElementById('selectAll');
    const deleteCheckboxes = document.querySelectorAll('.delete-checkbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const bulkDeleteForm = document.getElementById('bulkDeleteForm');
    const confirmBulkDeleteBtn = document.getElementById('confirmBulkDeleteBtn');

    // 全選択/解除
    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            const isChecked = this.checked;
            deleteCheckboxes.forEach(cb => cb.checked = isChecked);
            toggleBulkDeleteBtn();
        });
    }

    // 個別チェックボックスの監視
    deleteCheckboxes.forEach(cb => {
        cb.addEventListener('change', toggleBulkDeleteBtn);
    });

    // ボタンの有効化/無効化切り替え
    function toggleBulkDeleteBtn() {
        const checkedCount = document.querySelectorAll('.delete-checkbox:checked').length;
        bulkDeleteBtn.disabled = checkedCount === 0;
        
        // 全てチェックされていたら「全選択」もチェック、そうでなければ外す
        if (checkedCount === deleteCheckboxes.length && deleteCheckboxes.length > 0) {
            selectAllCheckbox.checked = true;
        } else {
            selectAllCheckbox.checked = false;
        }
    }

    // 一括削除ボタンクリック時
    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function() {
            const checkedCount = document.querySelectorAll('.delete-checkbox:checked').length;
            
            // モーダル表示切り替え
            document.getElementById('singleDeleteForm').style.display = 'none';
            document.getElementById('bulkDeleteActions').style.display = 'block';
            document.getElementById('deleteTargetTitle').textContent = checkedCount + '件のお知らせ';
            document.getElementById('deleteConfirmMsg').textContent = '選択したお知らせを削除してもよろしいですか？';
            
            var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
            deleteModal.show();
        });
    }

    // モーダル内の一括削除実行ボタン
    if (confirmBulkDeleteBtn) {
        confirmBulkDeleteBtn.addEventListener('click', function() {
            bulkDeleteForm.submit();
        });
    }
});

// --- 個別削除 (既存機能) ---
function confirmDelete(id, title) {
    // モーダル表示切り替え
    document.getElementById('singleDeleteForm').style.display = 'block';
    document.getElementById('bulkDeleteActions').style.display = 'none';
    
    document.getElementById('deleteTargetId').value = id;
    document.getElementById('deleteTargetTitle').textContent = title;
    document.getElementById('deleteConfirmMsg').textContent = '以下のお知らせを削除してもよろしいですか？';

    var deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>

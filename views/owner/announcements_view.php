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
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>過去のお知らせ</span>
                <button type="submit" form="bulkDeleteForm" class="btn btn-sm btn-danger" onclick="return confirm('選択したお知らせを本当に削除しますか？');">
                    選択したお知らせを削除
                </button>
            </div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted">お知らせはありません。</p>
                <?php else: ?>
                    <form method="post" id="bulkDeleteForm">
                        <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                        <input type="hidden" name="action" value="delete">
                        
                        <div class="mb-2 form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label" for="selectAll">全て選択</label>
                        </div>

                        <div class="list-group">
                            <?php foreach ($announcements as $a): ?>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between align-items-start">
                                        <div class="me-3 mt-1">
                                            <input class="form-check-input delete-checkbox" type="checkbox" name="delete_ids[]" value="<?php echo h($a['id']); ?>">
                                        </div>
                                        <div style="flex: 1;">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h5 class="mb-1"><?php echo h($a['title']); ?></h5>
                                                <small class="text-muted"><?php echo h($a['created_at']); ?></small>
                                            </div>
                                            <p class="mb-1" style="white-space: pre-wrap;"><?php echo h($a['content']); ?></p>
                                        </div>
                                    </div>
                                    <!-- 個別削除ボタン（機能的には一括削除と同じアクションを使うが、パラメータを変えてもよい。
                                         ここではUI上はチェックボックス推奨だが、既存ボタンを残すなら button type="submit" name="delete_id" value="..." にする）-->
                                    <div class="text-end mt-2">
                                        <button type="submit" name="delete_id" value="<?php echo h($a['id']); ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('このお知らせを削除しますか？');">削除</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 全選択チェックボックス
    const selectAllInfo = document.getElementById('selectAll');
    const checkboxesInfo = document.querySelectorAll('.delete-checkbox');

    if(selectAllInfo) {
        selectAllInfo.addEventListener('change', function() {
            checkboxesInfo.forEach(cb => {
                cb.checked = selectAllInfo.checked;
            });
        });
    }
});
</script>

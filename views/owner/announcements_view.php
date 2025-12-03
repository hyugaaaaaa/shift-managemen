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
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                    <input type="hidden" name="action" value="create">
                    <div class="mb-3">
                        <label for="title" class="form-label">タイトル</label>
                        <input type="text" class="form-control" id="title" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">本文</label>
                        <textarea class="form-control" id="content" name="content" rows="4" required></textarea>
                        <div class="form-text">作成と同時に、LINE連携済みの全ユーザーに通知が送信されます。</div>
                    </div>
                    <button type="submit" class="btn btn-primary">作成して通知</button>
                </form>
            </div>
        </div>

        <!-- お知らせ一覧 -->
        <div class="card">
            <div class="card-header">
                過去のお知らせ
            </div>
            <div class="card-body">
                <?php if (empty($announcements)): ?>
                    <p class="text-muted">お知らせはありません。</p>
                <?php else: ?>
                    <div class="list-group">
                        <?php foreach ($announcements as $a): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1"><?php echo h($a['title']); ?></h5>
                                    <small class="text-muted"><?php echo h($a['created_at']); ?></small>
                                </div>
                                <p class="mb-1" style="white-space: pre-wrap;"><?php echo h($a['content']); ?></p>
                                <form method="post" class="mt-2 text-end" onsubmit="return confirm('本当に削除しますか？');">
                                    <input type="hidden" name="csrf_token" value="<?php echo h(generate_csrf_token()); ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo h($a['id']); ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">削除</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

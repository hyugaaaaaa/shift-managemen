<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                ユーザー設定
            </div>
            <div class="card-body">
                <p>以下のアカウント設定を変更できます。</p>
                
                <div class="d-grid gap-2 col-md-6 mx-auto">
                    
                    <?php if ($_SESSION['user_type'] === 'owner'): ?>
                    <div class="mb-3 d-lg-none">
                        <h6 class="text-secondary small fw-bold"><i class="bi bi-grid-fill me-1"></i> 管理メニュー</h6>
                        <a href="<?php echo BASE_PATH; ?>/owner/monthly_hours.php" class="btn btn-outline-secondary w-100 mb-2 text-start">
                            <i class="bi bi-calculator me-2"></i>給与集計
                        </a>
                        <a href="<?php echo BASE_PATH; ?>/owner/export_data.php" class="btn btn-outline-secondary w-100 mb-2 text-start">
                            <i class="bi bi-file-earmark-arrow-down me-2"></i>データ出力
                        </a>
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary w-100 mb-2 text-start dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-gear-wide-connected me-2"></i>システム管理
                            </button>
                            <ul class="dropdown-menu w-100">
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/holidays.php">定休日設定</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/announcements.php">お知らせ管理</a></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/shift_templates.php">シフトパターン設定</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo BASE_PATH; ?>/owner/system_settings.php">システム設定</a></li>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <hr class="my-3">

                    <a href="<?php echo BASE_PATH; ?>/change_password.php" class="btn btn-outline-primary">
                        <i class="bi bi-key"></i> パスワード変更
                    </a>
                    
                    <hr class="my-3">
                    
                    <a href="<?php echo BASE_PATH; ?>/logout.php" class="btn btn-outline-danger">
                        <i class="bi bi-box-arrow-right"></i> ログアウト
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

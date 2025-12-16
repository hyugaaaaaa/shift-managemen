<?php render_header('登録完了'); ?>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-sm border-success">
            <div class="card-header bg-success text-white">
                <h5 class="card-title mb-0"><i class="bi bi-check-circle-fill"></i> 登録が完了しました</h5>
            </div>
            <div class="card-body text-center p-5">
                <h3 class="mb-4">ようこそ、<?php echo h($company_name); ?> 様</h3>
                <p class="lead mb-4">アカウントの作成が完了しました。</p>

                <div class="alert alert-warning d-inline-block text-start p-4 mb-4">
                    <h5 class="alert-heading text-center fw-bold mb-3">あなたの企業の招待コード</h5>
                    <div class="display-4 text-center monospace fw-bold user-select-all bg-white p-2 border rounded">
                        <?php echo h($company_code); ?>
                    </div>
                    <hr>
                    <p class="mb-0 small">
                        このコード（<strong><?php echo h($company_code); ?></strong>）をスタッフに伝えてください。<br>
                        スタッフは登録時にこのコードを入力することで、あなたの企業に所属することができます。
                    </p>
                </div>

                <div class="d-grid gap-2 d-sm-flex justify-content-sm-center">
                    <a href="dashboard.php" class="btn btn-primary btn-lg px-5">ダッシュボードへ移動</a>
                </div>
                
                <p class="text-muted mt-4 small">
                    ※招待コードは管理画面の「システム設定」からいつでも確認できます。
                </p>
            </div>
        </div>
    </div>
</div>

<?php render_footer(); ?>

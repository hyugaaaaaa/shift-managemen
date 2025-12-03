<?php render_header('ヘルプ - シフト管理システム'); ?>

<div class="container mt-4">
    <h1 class="mb-4"><i class="bi bi-question-circle-fill text-primary"></i> ヘルプ</h1>

    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-light">
                    <h5 class="card-title mb-0">目次</h5>
                </div>
                <div class="card-body">
                    <ul>
                        <li><a href="#common">共通機能</a></li>
                        <li><a href="#parttime">アルバイトの方へ</a></li>
                        <li><a href="#owner">オーナー・管理者の方へ</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <section id="common" class="mb-5">
        <h2 class="border-bottom pb-2 mb-3">共通機能</h2>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title">ログイン・ログアウト</h5>
                <p class="card-text">
                    システムを利用するには、IDとパスワードを入力してログインしてください。<br>
                    利用が終わったら、画面右上のメニューから「ログアウト」を選択して終了してください。
                </p>
            </div>
        </div>
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title">ダッシュボード</h5>
                <p class="card-text">
                    ログイン直後に表示される画面です。<br>
                    カレンダー形式でシフトを確認できます。
                    <ul>
                        <li><span class="badge bg-primary">出: HH:MM</span> 出勤予定時刻</li>
                        <li><span class="badge bg-danger">退: HH:MM</span> 退勤予定時刻</li>
                        <li><span class="badge bg-secondary">定休日</span> お店の定休日</li>
                    </ul>
                </p>
            </div>
        </div>
    </section>

    <section id="parttime" class="mb-5">
        <h2 class="border-bottom pb-2 mb-3">アルバイトの方へ</h2>
        
        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-plus"></i> 希望提出</h5>
                <p class="card-text">
                    来月（翌月）のシフト希望を提出します。<br>
                    カレンダーの日付をクリックし、希望する時間帯を入力して「登録」ボタンを押してください。<br>
                    <ul>
                        <li>締め切り日を過ぎると提出できません。</li>
                        <li>定休日に設定されている日は選択できません。</li>
                        <li>既に提出済みのシフトと同じ内容（日時）では重複して提出できません。</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-check"></i> 確定シフト</h5>
                <p class="card-text">
                    オーナーによって確定されたシフトを確認できます。<br>
                    自分のシフトのみが表示されます。
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-clock-history"></i> 勤怠確認・修正</h5>
                <p class="card-text">
                    打刻した勤怠データの確認や修正依頼を行えます。<br>
                    打刻忘れや間違いがあった場合は、ここから修正申請を行ってください。
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-cash-coin"></i> 給与明細</h5>
                <p class="card-text">
                    確定した給与明細を確認できます。<br>
                    月ごとに表示され、詳細を確認することができます。
                </p>
            </div>
        </div>
    </section>

    <section id="owner" class="mb-5">
        <h2 class="border-bottom pb-2 mb-3">オーナー・管理者の方へ</h2>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-list-check"></i> 希望一覧</h5>
                <p class="card-text">
                    従業員から提出されたシフト希望を一覧で確認できます。<br>
                    ここからシフトの作成・確定作業を行います。
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-people"></i> 従業員管理</h5>
                <p class="card-text">
                    従業員アカウントの作成、編集、削除が行えます。<br>
                    削除された従業員はログインできなくなりますが、過去の勤怠データなどは保持されます（論理削除）。
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar3"></i> 月間時間</h5>
                <p class="card-text">
                    月ごとの従業員の総労働時間を確認できます。<br>
                    シフト作成時の調整や、労働時間の管理に利用します。
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-check-circle"></i> 勤怠承認</h5>
                <p class="card-text">
                    従業員からの勤怠修正申請を承認または却下します。<br>
                    未承認の申請がある場合、ここに表示されます。
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-gear"></i> システム設定</h5>
                <p class="card-text">
                    以下の設定を変更できます。
                    <ul>
                        <li><strong>シフト提出締め切り日</strong>: アルバイトが翌月のシフトを提出できる期限日。</li>
                        <li><strong>給与締め日</strong>: 給与計算の締め日（例: 31 = 末日）。</li>
                        <li><strong>給与支払日</strong>: 給与の支払日。</li>
                    </ul>
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-calendar-x"></i> 定休日設定</h5>
                <p class="card-text">
                    お店の定休日を設定します。<br>
                    設定された日は、アルバイトがシフト希望を提出できなくなります。<br>
                    ダッシュボードのカレンダーにも「定休日」として表示されます。
                </p>
            </div>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-file-earmark-arrow-down"></i> データ出力</h5>
                <p class="card-text">
                    給与明細やシフト表をCSV形式で出力し、サーバー（C:\shift_management）に保存します。<br>
                    保存されたファイルはエクセル等で開くことができます。
                </p>
            </div>
        </div>
    </section>

</div>

<?php render_footer(); ?>

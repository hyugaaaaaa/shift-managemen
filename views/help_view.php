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

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-line"></i> LINE通知連携</h5>
                <p class="card-text">
                    LINE連携を行うと、お店からのお知らせをLINEで受け取ることができます。
                    <ol>
                        <li>お店のLINE公式アカウントを友だち追加します。</li>
                        <li>トーク画面で、何かメッセージ（スタンプでも可）を送信してください。</li>
                        <li>自動返信であなたの「LINE User ID」が送られてきます。</li>
                        <li>本システムの「設定」メニューを開き、「LINE User ID」欄にIDを入力して保存します。</li>
                    </ol>
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
                    従業員ごとの月間の勤務時間、給与見込額を集計して表示します。<br>
                    <ul>
                        <li>月ごとの切り替えが可能です。</li>
                        <li>通常時間、深夜時間、交通費などが自動計算されます。</li>
                        <li>各従業員の「明細」ボタンから、詳細な給与明細プレビューを確認できます。</li>
                    </ul>
                    <small class="text-muted">※時給や交通費は、従業員編集画面での設定に基づきます。</small>
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

        <div class="card shadow-sm mb-3">
            <div class="card-body">
                <h5 class="card-title"><i class="bi bi-gear"></i> システム設定</h5>
                <p class="card-text">
                    給与の締め日・支払日や、LINE連携の設定を行えます。<br>
                    <strong>LINE連携の設定手順:</strong>
                    <ol>
                        <li>LINE Developersコンソールで「Messaging API」設定を開きます。</li>
                        <li>「Webhook設定」に以下のURLを入力し、「Webhookの利用」をオンにします。<br>
                            <code><?php echo (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . BASE_PATH; ?>/webhook.php</code>
                        </li>
                        <li>「チャンネルアクセストークン（長期）」と「チャンネルシークレット」を発行・確認し、本システムの「システム設定」に入力して保存してください。</li>
                    </ol>
                </p>
            </div>
        </div>
    </section>

</div>

<?php render_footer(); ?>

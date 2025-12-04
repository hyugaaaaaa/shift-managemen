<?php
/**
 * ヘルプ画面のビュー
 * 2カラムレイアウト（左サイドバー固定）
 */
render_header('ヘルプ'); ?>
</div><!-- Close default container -->

<div class="container-fluid">
    <div class="row">
        <!-- サイドバーナビゲーション -->
        <nav id="help-sidebar" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse position-fixed start-0 bottom-0 overflow-auto" style="top: 70px; z-index: 100;">
            <div class="position-sticky pt-3">
                <div class="accordion" id="sidebarAccordion">
                    
                    <!-- FAQ -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFaq">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFaq" aria-expanded="true" aria-controls="collapseFaq">
                                <i class="bi bi-question-circle me-2"></i>よくある質問
                            </button>
                        </h2>
                        <div id="collapseFaq" class="accordion-collapse collapse show" aria-labelledby="headingFaq" data-bs-parent="#sidebarAccordion">
                            <div class="accordion-body p-0">
                                <div class="list-group list-group-flush">
                                    <a class="list-group-item list-group-item-action small ps-4" href="#faq-password">パスワード忘れ</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#faq-shift">シフト変更</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 共通機能 -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCommon">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCommon" aria-expanded="false" aria-controls="collapseCommon">
                                <i class="bi bi-info-circle me-2"></i>共通機能
                            </button>
                        </h2>
                        <div id="collapseCommon" class="accordion-collapse collapse" aria-labelledby="headingCommon" data-bs-parent="#sidebarAccordion">
                            <div class="accordion-body p-0">
                                <div class="list-group list-group-flush">
                                    <a class="list-group-item list-group-item-action small ps-4" href="#common-login">ログイン・ログアウト</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#common-dashboard">ダッシュボード</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- アルバイトの方へ -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingParttime">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseParttime" aria-expanded="false" aria-controls="collapseParttime">
                                <i class="bi bi-person me-2"></i>アルバイトの方へ
                            </button>
                        </h2>
                        <div id="collapseParttime" class="accordion-collapse collapse" aria-labelledby="headingParttime" data-bs-parent="#sidebarAccordion">
                            <div class="accordion-body p-0">
                                <div class="list-group list-group-flush">
                                    <a class="list-group-item list-group-item-action small ps-4" href="#parttime-submit">希望提出</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#parttime-confirmed">確定シフト</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#parttime-attendance">勤怠確認・修正</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#parttime-payslip">給与明細</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- オーナー・管理者の方へ -->
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOwner">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOwner" aria-expanded="false" aria-controls="collapseOwner">
                                <i class="bi bi-person-badge me-2"></i>オーナーの方へ
                            </button>
                        </h2>
                        <div id="collapseOwner" class="accordion-collapse collapse" aria-labelledby="headingOwner" data-bs-parent="#sidebarAccordion">
                            <div class="accordion-body p-0">
                                <div class="list-group list-group-flush">
                                    <a class="list-group-item list-group-item-action small ps-4" href="#owner-requests">希望一覧</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#owner-templates">シフトパターン</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#owner-users">従業員管理</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#owner-monthly">月間時間</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#owner-export">データ出力</a>
                                    <a class="list-group-item list-group-item-action small ps-4" href="#owner-settings">システム設定</a>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </nav>


    <!-- メインコンテンツ -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <h1 class="h3 mb-4 border-bottom pb-2">ヘルプセンター</h1>

        <section id="faq" class="mb-5 scroll-section">
            <h2 class="h4 mb-3 text-primary"><i class="bi bi-question-circle"></i> よくある質問 (FAQ)</h2>
            <div class="accordion shadow-sm" id="faqAccordion">
                <div class="accordion-item" id="faq-password">
                    <h2 class="accordion-header" id="headingOne">
                        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            Q. パスワードを忘れました。
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <strong>A.</strong> オーナー（管理者）に連絡して、パスワードの再設定を依頼してください。セキュリティ上、ご自身でのリセット機能は提供していません。
                        </div>
                    </div>
                </div>
                <div class="accordion-item" id="faq-shift">
                    <h2 class="accordion-header" id="headingTwo">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            Q. 提出したシフトを変更したいです。
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                        <div class="accordion-body">
                            <strong>A.</strong> 締め切り前であれば、再度同じ日付で正しい時間を入力して提出し直すことで上書きできる場合があります（システム設定によります）。締め切り後や、既に確定してしまったシフトの変更は、直接オーナーに相談してください。
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="common" class="mb-5 scroll-section">
            <h2 class="h4 mb-3 text-primary"><i class="bi bi-info-circle"></i> 共通機能</h2>
            <div class="card shadow-sm mb-3" id="common-login">
                <div class="card-body">
                    <h5 class="card-title">ログイン・ログアウト</h5>
                    <p class="card-text">
                        システムを利用するには、IDとパスワードを入力してログインしてください。<br>
                        利用が終わったら、画面右上のメニューから「ログアウト」を選択して終了してください。
                    </p>
                </div>
            </div>
            <div class="card shadow-sm mb-3" id="common-dashboard">
                <div class="card-body">
                    <h5 class="card-title">ダッシュボード</h5>
                    <p class="card-text">
                        ログイン直後に表示される画面です。<br>
                        カレンダー形式でシフトを確認できます。
                        <ul class="mt-2">
                            <li><span class="badge bg-primary">出: HH:MM</span> 出勤予定時刻</li>
                            <li><span class="badge bg-danger">退: HH:MM</span> 退勤予定時刻</li>
                            <li><span class="badge bg-secondary">定休日</span> お店の定休日</li>
                        </ul>
                    </p>
                </div>
            </div>
        </section>

        <section id="parttime" class="mb-5 scroll-section">
            <h2 class="h4 mb-3 text-primary"><i class="bi bi-person"></i> アルバイトの方へ</h2>
            
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="parttime-submit">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-calendar-plus text-success"></i> 希望提出</h5>
                            <p class="card-text small">
                                来月（翌月）のシフト希望を提出します。カレンダーの日付をクリックし、時間を入力して登録します。
                                <br><span class="text-danger">※締め切り日を過ぎると提出できません。</span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="parttime-confirmed">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-calendar-check text-primary"></i> 確定シフト</h5>
                            <p class="card-text small">
                                オーナーによって確定された自分のシフトを確認できます。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="parttime-attendance">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-clock-history text-warning"></i> 勤怠確認・修正</h5>
                            <p class="card-text small">
                                打刻データの確認や修正依頼を行えます。打刻忘れや間違いがあった場合はここから申請してください。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="parttime-payslip">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-cash-coin text-success"></i> 給与明細</h5>
                            <p class="card-text small">
                                確定した給与明細を月ごとに確認できます。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="owner" class="mb-5 scroll-section">
            <h2 class="h4 mb-3 text-primary"><i class="bi bi-person-badge"></i> オーナー・管理者の方へ</h2>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="owner-requests">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-list-check"></i> 希望一覧</h5>
                            <p class="card-text small">
                                提出されたシフト希望を一覧確認し、シフトの作成・確定作業を行います。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="owner-templates">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-grid-3x3-gap"></i> シフトパターン設定</h5>
                            <p class="card-text small">
                                よく使うシフトの時間帯（例：早番 09:00-17:00）を登録できます。従業員の入力負担を軽減します。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="owner-users">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-people"></i> 従業員管理</h5>
                            <p class="card-text small">
                                従業員アカウントの作成、編集、削除（論理削除）が行えます。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="owner-monthly">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-calendar3"></i> 月間時間</h5>
                            <p class="card-text small">
                                月間の勤務時間、給与見込額を集計表示します。詳細な明細プレビューも可能です。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="owner-export">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-file-earmark-arrow-down"></i> データ出力</h5>
                            <p class="card-text small">
                                給与明細やシフト表をCSV形式で出力し、サーバーに保存します。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm h-100" id="owner-settings">
                        <div class="card-body">
                            <h5 class="card-title"><i class="bi bi-gear"></i> システム設定</h5>
                            <p class="card-text small">
                                給与の締め日・支払日などの設定を行えます。
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>
</div>
</div>
<div class="container">
<?php render_footer(); ?>

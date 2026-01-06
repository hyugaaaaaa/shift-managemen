<?php
/**
 * ヘルプ画面のビュー
 * タブ切り替えレイアウト
 */
render_header('ヘルプ', true, ['help.css']); ?>
</div><!-- Close default container -->

<div class="container py-4">
    <div class="text-center mb-5">
        <h1 class="display-5 fw-bold text-primary mb-3">ヘルプセンター</h1>
        <p class="lead text-muted">操作方法やよくある質問をご確認いただけます。</p>
    </div>

    <!-- ナビゲーションタブ -->
    <ul class="nav nav-pills nav-fill mb-4 p-2 bg-white rounded-pill shadow-sm" id="helpTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active rounded-pill" id="tab-common" data-bs-toggle="pill" data-bs-target="#content-common" type="button" role="tab" aria-controls="content-common" aria-selected="true">
                <i class="bi bi-info-circle me-2"></i>共通機能
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill" id="tab-parttime" data-bs-toggle="pill" data-bs-target="#content-parttime" type="button" role="tab" aria-controls="content-parttime" aria-selected="false">
                <i class="bi bi-person me-2"></i>アルバイトの方
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill" id="tab-owner" data-bs-toggle="pill" data-bs-target="#content-owner" type="button" role="tab" aria-controls="content-owner" aria-selected="false">
                <i class="bi bi-person-badge me-2"></i>オーナーの方
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill" id="tab-faq" data-bs-toggle="pill" data-bs-target="#content-faq" type="button" role="tab" aria-controls="content-faq" aria-selected="false">
                <i class="bi bi-question-circle me-2"></i>よくある質問
            </button>
        </li>
    </ul>

    <!-- タブコンテンツ -->
    <div class="tab-content" id="helpTabContent">
        
        <!-- 共通機能 -->
        <div class="tab-pane fade show active" id="content-common" role="tabpanel" aria-labelledby="tab-common">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-body p-4">
                            <h3 class="h4 mb-4 text-primary border-bottom pb-2">基本操作</h3>
                            
                            <div class="mb-4">
                                <h5 class="fw-bold"><i class="bi bi-box-arrow-in-right text-success me-2"></i>ログイン・ログアウト</h5>
                                <p class="text-muted ms-4">
                                    システムを利用するには、<strong>企業コード</strong>、<strong>ユーザー名</strong>、<strong>パスワード</strong>を入力してログインしてください。<br>
                                    一度ログインすると、企業コードは一定期間保存されます。
                                    利用が終わったら、画面右上のメニューから「ログアウト」を選択して終了してください。安全のため、共有PCでは必ずログアウトを行ってください。
                                </p>
                            </div>

                            <div class="mb-4">
                                <h5 class="fw-bold"><i class="bi bi-speedometer2 text-primary me-2"></i>ダッシュボード</h5>
                                <p class="text-muted ms-4">
                                    ログイン直後に表示されるホーム画面です。カレンダー形式でシフト状況を一目で確認できます。
                                </p>
                                <ul class="list-unstyled ms-4 bg-light p-3 rounded">
                                    <li class="mb-2"><span class="badge bg-primary">出: HH:MM</span> <strong>出勤予定</strong>：あなたのシフトが入っている時間です。</li>
                                    <li class="mb-2"><span class="badge bg-danger">退: HH:MM</span> <strong>退勤予定</strong>：シフトの終了時間です。</li>
                                    <li><span class="badge bg-secondary">定休日</span> <strong>定休日</strong>：お店がお休みの日です。</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- アルバイトの方 -->
        <div class="tab-pane fade" id="content-parttime" role="tabpanel" aria-labelledby="tab-parttime">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-calendar-plus text-success fs-4"></i>
                                </div>
                                <h4 class="h5 mb-0 fw-bold">シフト希望提出</h4>
                            </div>
                            <p class="text-muted">
                                来月（翌月）のシフト希望を提出します。スマホからでも操作可能です。<br>
                                カレンダーの日付をタップし、開始・終了時間を入力して登録してください。
                            </p>
                            <div class="alert alert-warning py-2 small">
                                <i class="bi bi-exclamation-triangle me-1"></i> 締め切り日を過ぎると提出できなくなります。
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-calendar-check text-primary fs-4"></i>
                                </div>
                                <h4 class="h5 mb-0 fw-bold">確定シフト確認</h4>
                            </div>
                            <p class="text-muted">
                                オーナーによって確定されたシフトスケジュールを確認できます。<br>
                                自分のシフトだけでなく、全体のシフト状況も（設定により）確認可能です。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-clock-history text-warning fs-4"></i>
                                </div>
                                <h4 class="h5 mb-0 fw-bold">勤怠確認・修正</h4>
                            </div>
                            <p class="text-muted">
                                実際の勤務時間（実績）を確認できます。<br>
                                もし打刻間違いやシフトと異なる勤務をした場合は、ここから修正依頼を出してください。
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm hover-shadow">
                        <div class="card-body p-4">
                            <div class="d-flex align-items-center mb-3">
                                <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                                    <i class="bi bi-cash-coin text-info fs-4"></i>
                                </div>
                                <h4 class="h5 mb-0 fw-bold">給与明細</h4>
                            </div>
                            <p class="text-muted">
                                確定した給与明細をWeb上で確認できます。<br>
                                PDFとして保存したり、印刷したりすることも可能です。（初回閲覧時に電子交付への同意が必要です）
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- オーナーの方 -->
        <div class="tab-pane fade" id="content-owner" role="tabpanel" aria-labelledby="tab-owner">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-list-check fs-1 text-primary mb-3"></i>
                            <h5 class="fw-bold">希望一覧・シフト作成</h5>
                            <p class="text-muted small">スタッフから提出された希望をもとに、シフトを作成・調整し、確定させます。シフトテンプレートを利用すると定型シフトを簡単に入力できます。</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-people fs-1 text-success mb-3"></i>
                            <h5 class="fw-bold">従業員管理</h5>
                            <p class="text-muted small">スタッフのアカウント作成、時給設定、スキル登録などを行います。</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body text-center p-4">
                            <i class="bi bi-calculator fs-1 text-warning mb-3"></i>
                            <h5 class="fw-bold">月間集計・給与</h5>
                            <p class="text-muted small">月ごとの勤務時間を集計し、深夜割増などを含めた給与計算を自動で行います。</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-start p-4">
                            <i class="bi bi-gear fs-2 text-secondary me-3"></i>
                            <div>
                                <h5 class="fw-bold">システム設定</h5>
                                <p class="text-muted mb-0">給与の締め日、支払日、シフト提出期限、<strong>パスワードポリシー</strong>（最小文字数など）を店舗のルールに合わせて設定します。</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-start p-4">
                            <i class="bi bi-file-earmark-arrow-down fs-2 text-dark me-3"></i>
                            <div>
                                <h5 class="fw-bold">データ出力</h5>
                                <p class="text-muted mb-0">給与データやシフト表をCSV形式でダウンロードし、保管や他システム連携に利用できます。</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- 追加機能: お知らせ・定休日 -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body d-flex align-items-start p-4">
                            <i class="bi bi-megaphone fs-2 text-warning me-3"></i>
                            <div>
                                <h5 class="fw-bold">お知らせ・定休日管理</h5>
                                <p class="text-muted mb-0">スタッフへのお知らせ配信（メール通知可）や、店舗の定休日設定を行えます。</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- FAQ -->
        <div class="tab-pane fade" id="content-faq" role="tabpanel" aria-labelledby="tab-faq">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="accordion shadow-sm" id="faqAccordion">
                        <div class="accordion-item border-0 mb-3 rounded overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Q. パスワードを忘れました。ログインできません。
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-light text-muted">
                                    <strong>A.</strong> ログイン画面の「パスワードをお忘れですか？」リンクから、パスワードリセット手続きを行ってください（メールアドレスの登録が必要です）。<br>
                                    メールアドレスが未登録の場合や、リンクから操作できない場合は、オーナー（管理者）に連絡してください。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 rounded overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Q. 提出したシフトを変更したいです。
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-light text-muted">
                                    <strong>A.</strong> <strong>締め切り日前であれば</strong>、再度同じ日付を選択し、正しい時間を入力して「登録」することで希望を上書き修正できます。<br>
                                    <strong>締め切り日を過ぎている場合</strong>や、既に確定したシフトを変更したい場合は、システム上からは操作できません。直接オーナーに相談してください。
                                </div>
                            </div>
                        </div>
                        <div class="accordion-item border-0 mb-3 rounded overflow-hidden shadow-sm">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button collapsed fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Q. 給与の計算がおかしい気がします。
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#faqAccordion">
                                <div class="accordion-body bg-light text-muted">
                                    <strong>A.</strong> 「勤怠確認」ページで、その月の打刻実績や休憩時間が正しく記録されているか確認してください。<br>
                                    本システムでは、<strong>深夜時間帯（22:00〜05:00）</strong>は自動的に割増賃金（1.25倍）で計算されます。間違いがある場合はオーナーに修正を依頼してください。
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<div class="container">
<?php render_footer(); ?>

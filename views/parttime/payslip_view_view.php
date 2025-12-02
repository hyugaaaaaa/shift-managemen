<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>給与明細 | <?php echo htmlspecialchars($user['username']); ?> | <?php echo htmlspecialchars($target_month); ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&family=Noto+Sans+JP:wght@400;500;700&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="<?php echo BASE_PATH; ?>/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>

<div class="action-buttons no-print">
    <button onclick="window.print()" class="btn btn-primary btn-fab" title="印刷">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
        </svg>
    </button>
    <button onclick="window.close()" class="btn btn-secondary btn-fab" title="閉じる">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
        </svg>
    </button>
</div>

<div class="payslip-container">
    <div class="payslip-header">
        <div>
            <h1 class="payslip-title">給与明細書</h1>
            <div class="company-name">株式会社シフトマネジメント</div>
        </div>
        <div class="text-end">
            <div style="font-size: 0.9rem; opacity: 0.9;">支給年月</div>
            <div style="font-size: 1.5rem; font-weight: 700;"><?php echo date('Y年n月', strtotime($target_month . '-01')); ?></div>
        </div>
    </div>

    <div class="payslip-body">
        <div class="info-grid">
            <div class="info-item">
                <label>氏名</label>
                <div class="value"><?php echo htmlspecialchars($user['username']); ?> 様</div>
            </div>
            <div class="info-item">
                <label>支給日</label>
                <div class="value"><?php echo htmlspecialchars($payment_date); ?></div>
            </div>
            <div class="info-item">
                <label>対象期間</label>
                <div class="value"><?php echo htmlspecialchars($start_date); ?> ～ <?php echo htmlspecialchars($end_date); ?></div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <h3 class="section-title">勤怠実績</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>出勤日数</th>
                            <th class="text-end">通常時間</th>
                            <th class="text-end">深夜時間</th>
                            <th class="text-end">総労働時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><?php echo $days_worked; ?> 日</td>
                            <td class="text-end"><?php echo round($normal_minutes / 60, 2); ?> h</td>
                            <td class="text-end"><?php echo round($night_minutes / 60, 2); ?> h</td>
                            <td class="text-end fw-bold"><?php echo round(($normal_minutes + $night_minutes) / 60, 2); ?> h</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h3 class="section-title">支給</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>項目</th>
                            <th class="text-end">金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                基本給
                                <div class="text-muted small">@<?php echo number_format($rate); ?> × <?php echo round($normal_minutes / 60, 2); ?>h</div>
                            </td>
                            <td class="text-end">¥<?php echo number_format($pay_normal); ?></td>
                        </tr>
                        <tr>
                            <td>
                                深夜割増
                                <div class="text-muted small">@<?php echo number_format($rate * 1.25); ?> × <?php echo round($night_minutes / 60, 2); ?>h</div>
                            </td>
                            <td class="text-end">¥<?php echo number_format($pay_night); ?></td>
                        </tr>
                        <tr>
                            <td>
                                交通費
                                <div class="text-muted small">@<?php echo number_format($transport); ?> × <?php echo $days_worked; ?>日</div>
                            </td>
                            <td class="text-end">¥<?php echo number_format($pay_transport); ?></td>
                        </tr>
                        <tr style="background-color: #f8f9fa;">
                            <td class="fw-bold">支給計</td>
                            <td class="text-end fw-bold">¥<?php echo number_format($total_pay); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-6">
                <h3 class="section-title">控除</h3>
                <table class="table-custom">
                    <thead>
                        <tr>
                            <th>項目</th>
                            <th class="text-end">金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>所得税</td>
                            <td class="text-end">¥0</td>
                        </tr>
                        <tr>
                            <td>社会保険料</td>
                            <td class="text-end">¥0</td>
                        </tr>
                        <tr>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                        </tr>
                        <tr style="background-color: #f8f9fa;">
                            <td class="fw-bold">控除計</td>
                            <td class="text-end fw-bold">¥0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="total-section">
            <div class="total-label">差引支給額</div>
            <div class="total-amount">¥<?php echo number_format($total_pay); ?></div>
        </div>

        <div class="footer-note">
            <p>※本明細書は電子交付されたものです。ご不明な点がございましたら管理者までお問い合わせください。</p>
        </div>
    </div>
</div>

</body>
</html>

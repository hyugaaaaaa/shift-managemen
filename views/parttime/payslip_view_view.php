<!doctype html>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>給与明細_<?php echo htmlspecialchars($user['username']); ?>_<?php echo $target_month; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
    body { background: #eee; padding: 20px; }
    .payslip { background: #fff; padding: 40px; max-width: 800px; margin: 0 auto; border: 1px solid #ddd; }
    @media print {
        body { background: #fff; padding: 0; }
        .payslip { border: none; margin: 0; width: 100%; max-width: none; }
        .no-print { display: none !important; }
    }
    .table-sm th, .table-sm td { padding: 0.5rem; }
    .header-title { border-bottom: 2px solid #000; margin-bottom: 20px; padding-bottom: 10px; }
</style>
</head>
<body>

<div class="text-center mb-3 no-print">
    <button onclick="window.print()" class="btn btn-primary">印刷する</button>
    <button onclick="window.close()" class="btn btn-secondary">閉じる</button>
</div>

<div class="payslip">
    <div class="d-flex justify-content-between align-items-end header-title">
        <h1 class="h3 m-0">給与明細書</h1>
        <div class="text-end">
            <div>支給年月: <?php echo date('Y年n月', strtotime($target_month . '-01')); ?></div>
            <div>支給日: <?php echo $payment_date; ?></div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-6">
            <h4 class="h5 border-bottom pb-1">氏名: <?php echo htmlspecialchars($user['username']); ?> 様</h4>
        </div>
        <div class="col-6 text-end">
            <p class="mb-0">対象期間: <?php echo $start_date; ?> ～ <?php echo $end_date; ?></p>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h5 class="bg-light p-2 border">勤怠項目</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th class="text-center">出勤日数</th>
                        <th class="text-center">通常時間</th>
                        <th class="text-center">深夜時間</th>
                        <th class="text-center">総労働時間</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-end"><?php echo $days_worked; ?> 日</td>
                        <td class="text-end"><?php echo round($normal_minutes / 60, 2); ?> h</td>
                        <td class="text-end"><?php echo round($night_minutes / 60, 2); ?> h</td>
                        <td class="text-end"><?php echo round(($normal_minutes + $night_minutes) / 60, 2); ?> h</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <h5 class="bg-light p-2 border">支給項目</h5>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>項目</th>
                        <th class="text-center">単価 / 計算式</th>
                        <th class="text-end">金額</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>基本給</td>
                        <td class="text-center">@<?php echo number_format($rate); ?> × <?php echo round($normal_minutes / 60, 2); ?>h</td>
                        <td class="text-end">¥<?php echo number_format($pay_normal); ?></td>
                    </tr>
                    <tr>
                        <td>深夜割増</td>
                        <td class="text-center">@<?php echo number_format($rate * 1.25); ?> × <?php echo round($night_minutes / 60, 2); ?>h</td>
                        <td class="text-end">¥<?php echo number_format($pay_night); ?></td>
                    </tr>
                    <tr>
                        <td>交通費</td>
                        <td class="text-center">@<?php echo number_format($transport); ?> × <?php echo $days_worked; ?>日</td>
                        <td class="text-end">¥<?php echo number_format($pay_transport); ?></td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td colspan="2" class="text-end">総支給額</td>
                        <td class="text-end">¥<?php echo number_format($total_pay); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <h5 class="bg-light p-2 border">控除項目</h5>
            <table class="table table-bordered table-sm">
                <tbody>
                    <tr>
                        <td>所得税</td>
                        <td class="text-end">¥0</td>
                    </tr>
                    <tr>
                        <td>社会保険料</td>
                        <td class="text-end">¥0</td>
                    </tr>
                    <tr class="table-light fw-bold">
                        <td class="text-end">控除計</td>
                        <td class="text-end">¥0</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12 text-end">
            <h3 class="border-top pt-2">差引支給額: ¥<?php echo number_format($total_pay); ?></h3>
        </div>
    </div>
    
    <div class="mt-5 text-center text-muted" style="font-size: 0.8rem;">
        <p>※本明細書は電子交付されたものです。</p>
    </div>
</div>

</body>
</html>

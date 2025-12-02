<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠確認・修正 - アルバイトシフト管理</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/css/style.css">
    <style>
        .attendance-table th, .attendance-table td { padding: 10px; border: 1px solid #ddd; text-align: center; vertical-align: middle; }
        .status-pending { color: #fd7e14; font-weight: bold; }
        .status-approved { color: #198754; font-weight: bold; }
        .diff-alert { background-color: #fff3cd; }
    </style>
</head>
<body>
    <?php render_header('勤怠確認・修正'); ?>

    <div class="container">
        <h1 class="mb-4">勤怠確認・修正</h1>
        
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="?month=<?= date('Y-m', strtotime("$month -1 month")) ?>" class="btn btn-outline-primary">&laquo; 前月</a>
            <h3 class="m-0"><?= htmlspecialchars($month) ?></h3>
            <a href="?month=<?= date('Y-m', strtotime("$month +1 month")) ?>" class="btn btn-outline-primary">次月 &raquo;</a>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover attendance-table">
                <thead class="table-light">
                    <tr>
                        <th>日付</th>
                        <th>シフト予定</th>
                        <th>勤怠実績</th>
                        <th>状態</th>
                        <th>備考</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($dates as $date): 
                        $record = $my_records[$date] ?? [];
                        $sched_start = ($record['type'] ?? '') === 'schedule' || isset($record['schedule_id']) ? ($record['start'] ?? '-') : '-';
                        $sched_end = ($record['type'] ?? '') === 'schedule' || isset($record['schedule_id']) ? ($record['end'] ?? '-') : '-';
                        
                        // 実績データがある場合
                        $att_start = (($record['type'] ?? '') === 'attendance') ? ($record['start'] ?? '') : '';
                        $att_end = (($record['type'] ?? '') === 'attendance') ? ($record['end'] ?? '') : '';
                        $status = (($record['type'] ?? '') === 'attendance') ? ($record['status'] ?? '') : '';
                        $is_approved = (($record['type'] ?? '') === 'attendance') ? ($record['is_approved'] ?? false) : false;
                        $notes = (($record['type'] ?? '') === 'attendance') ? ($record['notes'] ?? '') : '';
                        
                        // 差異があるか簡易判定（予定ありかつ実績なし、または時間不一致）
                        $has_diff = false;
                        if ($sched_start !== '-' && $att_start === '') $has_diff = true; // 予定あり実績なし
                        if ($att_start !== '' && $sched_start !== '-' && $att_start !== $sched_start) $has_diff = true;
                        
                        $row_class = $has_diff ? 'diff-alert' : '';
                        
                        // 土日の色付け用クラス
                        $w = (int)date('w', strtotime($date));
                        $date_class = '';
                        if ($w === 0) $date_class = 'text-danger'; // 日
                        elseif ($w === 6) $date_class = 'text-primary'; // 土
                    ?>
                    <tr class="<?= $row_class ?>">
                        <td class="<?= $date_class ?>"><?= date('j日', strtotime($date)) ?> <?= get_day_of_week_ja($date) ?></td>
                        <td><?= $sched_start !== '-' ? substr($sched_start, 0, 5) . ' - ' . substr($sched_end, 0, 5) : '-' ?></td>
                        <td>
                            <?php if ($att_start): ?>
                                <?= substr($att_start, 0, 5) ?> - <?= substr($att_end, 0, 5) ?>
                            <?php elseif ($status === 'absent'): ?>
                                <span class="badge bg-danger">欠勤</span>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (($record['type'] ?? '') === 'attendance'): ?>
                                <?php if ($is_approved): ?>
                                    <span class="status-approved"><i class="bi bi-check-circle-fill"></i> 承認済</span>
                                <?php else: ?>
                                    <span class="status-pending"><i class="bi bi-hourglass-split"></i> 承認待ち</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td class="text-start"><?= nl2br(htmlspecialchars($notes ?? '')) ?></td>
                        <td>
                            <?php if (!$is_approved): ?>
                                <button type="button" class="btn btn-sm btn-primary" 
                                    onclick="openEditModal('<?= $date ?>', '<?= $att_start ?>', '<?= $att_end ?>', '<?= $status ?>', '<?= htmlspecialchars($notes ?? '', ENT_QUOTES) ?>')">
                                    <i class="bi bi-pencil"></i> 修正
                                </button>
                            <?php else: ?>
                                <span class="text-muted small"><i class="bi bi-lock"></i> 変更不可</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 修正モーダル (Bootstrap 5) -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">勤怠実績修正</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p id="modalDateDisplay" class="fw-bold mb-3"></p>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="date" id="modalDate">
                        
                        <div class="mb-3">
                            <label for="modalStatus" class="form-label">状態</label>
                            <select name="status" id="modalStatus" class="form-select" onchange="toggleTimeInputs()">
                                <option value="present">出勤</option>
                                <option value="absent">欠勤</option>
                                <option value="late">遅刻</option>
                                <option value="early_leave">早退</option>
                                <option value="paid_leave">有給</option>
                            </select>
                        </div>

                        <div id="timeInputs" class="row">
                            <div class="col-6 mb-3">
                                <label for="modalClockIn" class="form-label">出勤時間</label>
                                <input type="time" name="clock_in" id="modalClockIn" class="form-control">
                            </div>
                            <div class="col-6 mb-3">
                                <label for="modalClockOut" class="form-label">退勤時間</label>
                                <input type="time" name="clock_out" id="modalClockOut" class="form-control">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="modalNotes" class="form-label">備考</label>
                            <textarea name="notes" id="modalNotes" class="form-control" rows="3" placeholder="修正理由などを入力してください"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">保存する</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        let editModal;
        
        document.addEventListener('DOMContentLoaded', function() {
            editModal = new bootstrap.Modal(document.getElementById('editModal'));
        });

        function openEditModal(date, start, end, status, notes) {
            document.getElementById('modalDate').value = date;
            document.getElementById('modalDateDisplay').innerText = date + ' の勤怠修正';
            document.getElementById('modalClockIn').value = start ? start.substring(0, 5) : '';
            document.getElementById('modalClockOut').value = end ? end.substring(0, 5) : '';
            document.getElementById('modalStatus').value = status || 'present';
            document.getElementById('modalNotes').value = notes;
            
            toggleTimeInputs();
            editModal.show();
        }

        function toggleTimeInputs() {
            const status = document.getElementById('modalStatus').value;
            const timeInputs = document.getElementById('timeInputs');
            if (status === 'absent' || status === 'paid_leave') {
                timeInputs.style.display = 'none';
            } else {
                timeInputs.style.display = 'flex'; // Bootstrap row uses flex
            }
        }
    </script>
    </script>
    <?php render_footer(); ?>

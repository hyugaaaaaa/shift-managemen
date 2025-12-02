<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>勤怠承認 - シフト管理システム</title>
    <link rel="stylesheet" href="<?= BASE_PATH ?>/css/style.css">
    <style>
        .approval-table th, .approval-table td { padding: 8px; border: 1px solid #ddd; text-align: center; font-size: 0.9em; vertical-align: middle; }
        .user-section { margin-bottom: 30px; border: 1px solid #ccc; padding: 15px; border-radius: 5px; background-color: #f9f9f9; }
        .diff-alert { background-color: #fff3cd; }
        .status-approved { color: #198754; font-weight: bold; }
        .status-pending { color: #fd7e14; font-weight: bold; }
    </style>
</head>
<body>
    <?php render_header('勤怠承認'); ?>

    <div class="container">
        <h1 class="mb-4">勤怠承認画面</h1>
        
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

        <?php foreach ($users as $user): ?>
            <div class="user-section shadow-sm">
                <h3 class="border-bottom pb-2 mb-3"><?= htmlspecialchars($user['username']) ?></h3>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover approval-table">
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
                            <?php 
                            $user_records = $all_records[$user['user_id']] ?? [];
                            // 日付順にソート
                            ksort($user_records);
                            
                            foreach ($user_records as $date => $record):
                                // 予定も実績もない日はスキップ（カレンダー全て出すと長いので）
                                // ただし、予定があるか、実績がある場合は表示
                                if (($record['type'] ?? '') === 'none') continue; 

                                $sched_start = ($record['type'] ?? '') === 'schedule' || isset($record['schedule_id']) ? ($record['start'] ?? '-') : '-';
                                $sched_end = ($record['type'] ?? '') === 'schedule' || isset($record['schedule_id']) ? ($record['end'] ?? '-') : '-';
                                
                                $att_start = (($record['type'] ?? '') === 'attendance') ? ($record['start'] ?? '') : '';
                                $att_end = (($record['type'] ?? '') === 'attendance') ? ($record['end'] ?? '') : '';
                                $status = (($record['type'] ?? '') === 'attendance') ? ($record['status'] ?? '') : '';
                                $is_approved = (($record['type'] ?? '') === 'attendance') ? ($record['is_approved'] ?? false) : false;
                                $notes = (($record['type'] ?? '') === 'attendance') ? ($record['notes'] ?? '') : '';
                                $att_id = (($record['type'] ?? '') === 'attendance') ? ($record['attendance_id'] ?? '') : '';

                                $has_diff = false;
                                if ($sched_start !== '-' && $att_start === '') $has_diff = true;
                                if ($att_start !== '' && $sched_start !== '-' && $att_start !== $sched_start) $has_diff = true;
                                
                                // 承認待ちはハイライト
                                $row_class = '';
                                if (($record['type'] ?? '') === 'attendance' && !$is_approved) $row_class = 'diff-alert';
                                
                                // 土日の色付け
                                $w = (int)date('w', strtotime($date));
                                $date_class = '';
                                if ($w === 0) $date_class = 'text-danger';
                                elseif ($w === 6) $date_class = 'text-primary';
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
                                            <span class="status-pending"><i class="bi bi-hourglass-split"></i> 未承認</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-start"><?= nl2br(htmlspecialchars($notes ?? '')) ?></td>
                                <td>
                                    <?php if (($record['type'] ?? '') === 'attendance' && !$is_approved): ?>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                                            <input type="hidden" name="action" value="approve">
                                            <input type="hidden" name="attendance_id" value="<?= $att_id ?>">
                                            <button type="submit" class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> 承認</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if (($record['type'] ?? '') === 'attendance'): ?>
                                        <button type="button" class="btn btn-sm btn-outline-primary" 
                                            onclick="openEditModal('<?= $att_id ?>', '<?= $date ?>', '<?= $att_start ?>', '<?= $att_end ?>', '<?= $status ?>', '<?= htmlspecialchars($notes ?? '', ENT_QUOTES) ?>')">
                                            <i class="bi bi-pencil"></i> 修正
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- 修正モーダル (Bootstrap 5) -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">勤怠実績修正（オーナー）</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post" action="">
                    <div class="modal-body">
                        <p id="modalDateDisplay" class="fw-bold mb-3"></p>
                        <input type="hidden" name="csrf_token" value="<?= generate_csrf_token() ?>">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="attendance_id" id="modalAttId">
                        
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
                            <textarea name="notes" id="modalNotes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                        <button type="submit" class="btn btn-primary">修正して承認</button>
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

        function openEditModal(id, date, start, end, status, notes) {
            document.getElementById('modalAttId').value = id;
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
                timeInputs.style.display = 'flex';
            }
        }
    </script>
    </script>
    <?php render_footer(); ?>

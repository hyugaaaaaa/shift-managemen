<?php
require_once __DIR__ . '/../config.php';
session_start();
require_once __DIR__ . '/../template.php';

// オーナー権限チェック
if (empty($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'owner') {
    header('Location: ' . BASE_PATH . '/index.php');
    exit;
}

$pdo = getPDO();
$error = '';
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRFチェック
    if (!validate_csrf_token($_POST['csrf_token'] ?? '')) {
        $error = 'セッションが無効です。もう一度お試しください。';
    } else {
        $export_type = $_POST['export_type'] ?? '';
        $month = $_POST['month'] ?? date('Y-m');
        $save_dir = 'C:\\shift_management\\';

        // ディレクトリが存在しない場合は作成（念のため）
        if (!file_exists($save_dir)) {
            if (!mkdir($save_dir, 0777, true)) {
                $error = '保存先ディレクトリの作成に失敗しました: ' . $save_dir;
            }
        }

        if (empty($error)) {
            if ($export_type === 'payslip') {
                // 給与明細CSV出力
                $start_date = date('Y-m-01', strtotime($month . '-01'));
                $end_date = date('Y-m-t', strtotime($start_date));

                // 全従業員のデータを取得
                $stmt = $pdo->prepare("SELECT user_id, username, hourly_rate, transportation_expense FROM users WHERE user_type = 'part-time' AND is_deleted = 0");
                $stmt->execute();
                $users = $stmt->fetchAll();

                // CSVヘッダー
                $csv_data = "従業員ID,氏名,対象月,通常勤務時間(分),深夜勤務時間(分),基本給,深夜割増,交通費,総支給額\n";

                foreach ($users as $u) {
                    // 勤務時間計算 (簡易版: 本来は monthly_hours.php 等とロジック共有すべき)
                    // ここでは get_merged_work_records を利用して計算
                    $records = get_merged_work_records($pdo, $start_date, $end_date, $u['user_id']);
                    $total_normal = 0;
                    $total_night = 0;
                    $days_worked = 0;

                    if (isset($records[$u['user_id']])) {
                        foreach ($records[$u['user_id']] as $date => $date_records) {
                            $has_worked_today = false;
                            foreach ($date_records as $r) {
                                if (($r['type'] === 'attendance' && $r['status'] === 'present') || ($r['type'] === 'schedule' && $r['status'] === 'scheduled')) {
                                     if ($r['type'] === 'attendance' && $r['start'] && $r['end']) {
                                        $mins = calculate_shift_minutes($date, $r['start'], $r['end']);
                                        $total_normal += $mins['normal_minutes'];
                                        $total_night += $mins['night_minutes'];
                                        $has_worked_today = true;
                                     } elseif ($r['type'] === 'schedule' && $r['start'] && $r['end']) {
                                         // 予定のみの場合（実績がない場合）の計算ロジックも必要であればここに追加
                                         // get_merged_work_records の仕様上、実績があれば予定は入ってこないはずだが、
                                         // 念のため条件分岐を残しておく
                                        $mins = calculate_shift_minutes($date, $r['start'], $r['end']);
                                        $total_normal += $mins['normal_minutes'];
                                        $total_night += $mins['night_minutes'];
                                        $has_worked_today = true;
                                     }
                                }
                            }
                            if ($has_worked_today) {
                                $days_worked++;
                            }
                        }
                    }

                    $salary = calculate_salary_amount($total_normal, $total_night, $u['hourly_rate']);
                    $transport = $days_worked * $u['transportation_expense'];
                    $total_pay = $salary['subtotal'] + $transport;

                    $line = [
                        $u['user_id'],
                        $u['username'],
                        $month,
                        $total_normal,
                        $total_night,
                        $salary['pay_normal'],
                        $salary['pay_night'],
                        $transport,
                        $total_pay
                    ];
                    // CSVエスケープ処理
                    $line = array_map(function($v){ return '"' . str_replace('"', '""', $v) . '"'; }, $line);
                    $csv_data .= implode(',', $line) . "\n";
                }

                // ファイル保存
                $filename = 'payslips_' . $month . '.csv';
                $filepath = $save_dir . $filename;
                
                if (file_put_contents($filepath, $csv_data) !== false) {
                    $msg = '給与明細データを保存しました: ' . $filepath;
                } else {
                    $error = 'ファイルの保存に失敗しました。';
                }

            } elseif ($export_type === 'shifts') {
                // シフト表CSV出力
                $start_date = date('Y-m-01', strtotime($month . '-01'));
                $end_date = date('Y-m-t', strtotime($start_date));

                // シフトデータ取得
                $sql = 'SELECT s.*, u.username FROM shifts_scheduled s JOIN users u ON s.user_id = u.user_id WHERE s.shift_date BETWEEN ? AND ? ORDER BY s.shift_date, s.start_time';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$start_date, $end_date]);
                $rows = $stmt->fetchAll();

                $csv_data = "日付,従業員名,開始時刻,終了時刻\n";
                foreach ($rows as $r) {
                    $line = [
                        $r['shift_date'],
                        $r['username'],
                        substr($r['start_time'], 0, 5),
                        substr($r['end_time'], 0, 5)
                    ];
                    $line = array_map(function($v){ return '"' . str_replace('"', '""', $v) . '"'; }, $line);
                    $csv_data .= implode(',', $line) . "\n";
                }

                // ファイル保存
                $filename = 'shifts_' . $month . '.csv';
                $filepath = $save_dir . $filename;

                if (file_put_contents($filepath, $csv_data) !== false) {
                    $msg = 'シフト表データを保存しました: ' . $filepath;
                } else {
                    $error = 'ファイルの保存に失敗しました。';
                }
            }
        }
    }
}

require_once __DIR__ . '/../views/owner/export_data_view.php';

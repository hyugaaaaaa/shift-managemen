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

                // 全従業員のデータを取得（自社のみ）
                $company_id = get_current_company_id();
                $stmt = $pdo->prepare("SELECT user_id, company_user_id, username, hourly_rate, transportation_expense FROM users WHERE user_type = 'part-time' AND is_deleted = 0 AND company_id = ? ORDER BY company_user_id ASC");
                $stmt->execute([$company_id]);
                $users = $stmt->fetchAll();

                // CSVヘッダー
                $csv_data = "従業員ID,氏名,対象月,通常勤務時間(分),深夜勤務時間(分),基本給,深夜割増,交通費,総支給額\n";
                // MS Excel等で開くためSJIS変換
                $csv_data = mb_convert_encoding($csv_data, 'SJIS-win', 'UTF-8');

                foreach ($users as $u) {
                    // 勤務時間計算
                    $records = get_merged_work_records($pdo, $start_date, $end_date, $u['user_id']);
                    $total_normal = 0;
                    $total_night = 0;
                    $days_worked = 0;

                    if (isset($records[$u['user_id']])) {
                        foreach ($records[$u['user_id']] as $date => $date_records) {
                            $has_worked_today = false;
                            foreach ($date_records as $r) {
                                // 実績(attendance/present) または 予定(schedule/scheduled)
                                if (($r['type'] === 'attendance' && $r['status'] === 'present') || ($r['type'] === 'schedule' && $r['status'] === 'scheduled')) {
                                     // 実績優先ロジックは get_merged_work_records で処理済みだが、念のため両方チェック
                                     if ($r['start'] && $r['end']) {
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

                    // 給与計算
                    // calculate_salary_amount は [pay_normal, pay_night, subtotal] を返す (functions.php参照)
                    // subtotal = pay_normal + pay_night
                    $salary_info = calculate_salary_amount($total_normal, $total_night, $u['hourly_rate']);
                    $pay_basic = $salary_info['pay_normal'];
                    $pay_night_allowance = $salary_info['pay_night'];
                    $pay_transport = $days_worked * $u['transportation_expense'];
                    $grand_total = $salary_info['subtotal'] + $pay_transport;

                    $line = [
                        $u['company_user_id'] ?? $u['user_id'],
                        $u['username'],
                        $month,
                        sprintf('%.2f', $total_normal),
                        sprintf('%.2f', $total_night),
                        $pay_basic,
                        $pay_night_allowance,
                        $pay_transport,
                        $grand_total
                    ];
                    
                    // CSV Escape
                    $line = array_map(function($v){ return '"'.str_replace('"','""',$v).'"'; }, $line);
                    $csv_data .= mb_convert_encoding(implode(',', $line) . "\n", 'SJIS-win', 'UTF-8');
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

                // シフトデータ取得（自社のみ、usersテーブルと結合してcompany_idチェック）
                $company_id = get_current_company_id();
                $sql = 'SELECT s.*, u.username, u.company_user_id FROM shifts_scheduled s JOIN users u ON s.user_id = u.user_id WHERE s.shift_date BETWEEN ? AND ? AND u.company_id = ? ORDER BY u.company_user_id, s.shift_date, s.start_time';
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$start_date, $end_date, $company_id]);
                $rows = $stmt->fetchAll();

                $csv_data = "日付,社員番号,従業員名,開始時刻,終了時刻\n";
                $csv_data = mb_convert_encoding($csv_data, 'SJIS-win', 'UTF-8');

                foreach ($rows as $row) {
                    $line = [
                        $row['shift_date'],
                        $row['company_user_id'] ?? '',
                        $row['username'],
                        substr($row['start_time'], 0, 5),
                        substr($row['end_time'], 0, 5)
                    ];
                    $line = array_map(function($v){ return '"'.str_replace('"','""',$v).'"'; }, $line);
                    $csv_data .= mb_convert_encoding(implode(',', $line) . "\n", 'SJIS-win', 'UTF-8');
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

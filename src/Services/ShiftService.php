<?php

namespace App\Services;

use PDO;

class ShiftService
{
    /**
     * シフトの勤務時間を計算する
     * @param string $date シフト日付 (Y-m-d)
     * @param string $start_time 開始時刻 (H:i:s)
     * @param string $end_time 終了時刻 (H:i:s)
     * @return array [total_minutes, night_minutes, normal_minutes]
     */
    public function calculateMinutes(string $date, string $start_time, string $end_time): array
    {
        $start = strtotime($date . ' ' . $start_time);
        $end = strtotime($date . ' ' . $end_time);
        
        // 日またぎ対応
        if ($end <= $start) {
            $end += 86400;
        }
        
        $total_minutes = ($end - $start) / 60;
        $night_minutes = 0;
        
        // 深夜時間計算（22:00 - 05:00）
        for ($t = $start; $t < $end; $t += 60) {
            $H = (int)date('H', $t);
            if ($H >= 22 || $H < 5) {
                $night_minutes++;
            }
        }
        
        return [
            'total_minutes' => $total_minutes,
            'night_minutes' => $night_minutes,
            'normal_minutes' => $total_minutes - $night_minutes
        ];
    }

    /**
     * 指定期間の勤務実績データ（予定と実績をマージしたもの）を取得する
     * @param PDO $pdo
     * @param string $start_date
     * @param string $end_date
     * @param int|null $target_user_id 特定ユーザーのみ取得する場合に指定
     * @return array [user_id => [date => record]]
     */
    public function getMergedWorkRecords(PDO $pdo, string $start_date, string $end_date, ?int $target_user_id = null): array
    {
        // 1. シフト予定の取得
        $sql_sched = "SELECT * FROM shifts_scheduled WHERE shift_date BETWEEN ? AND ?";
        $params_sched = [$start_date, $end_date];
        if ($target_user_id) {
            $sql_sched .= " AND user_id = ?";
            $params_sched[] = $target_user_id;
        }
        $stmt = $pdo->prepare($sql_sched);
        $stmt->execute($params_sched);
        
        $schedules = [];
        foreach ($stmt->fetchAll() as $row) {
            $uid = $row['user_id'];
            $date = $row['shift_date'];
            if (!isset($schedules[$uid])) $schedules[$uid] = [];
            if (!isset($schedules[$uid][$date])) $schedules[$uid][$date] = [];
            
            $schedules[$uid][$date][] = [
                'type' => 'schedule',
                'start' => $row['start_time'],
                'end' => $row['end_time'],
                'schedule_id' => $row['schedule_id'],
                'status' => 'scheduled'
            ];
        }

        // 2. 勤怠実績の取得
        $sql_att = "SELECT * FROM attendance_records WHERE date BETWEEN ? AND ?";
        $params_att = [$start_date, $end_date];
        if ($target_user_id) {
            $sql_att .= " AND user_id = ?";
            $params_att[] = $target_user_id;
        }
        $stmt = $pdo->prepare($sql_att);
        $stmt->execute($params_att);
        
        $attendances = [];
        foreach ($stmt->fetchAll() as $row) {
            $uid = $row['user_id'];
            $date = $row['date'];
            
            $start = $row['clock_in_time'] ? date('H:i:s', strtotime($row['clock_in_time'])) : null;
            $end = $row['clock_out_time'] ? date('H:i:s', strtotime($row['clock_out_time'])) : null;
            
            if ($row['status'] === 'absent') {
                $start = null;
                $end = null;
            }

            if (!isset($attendances[$uid])) $attendances[$uid] = [];
            if (!isset($attendances[$uid][$date])) $attendances[$uid][$date] = [];
            
            $attendances[$uid][$date][] = [
                'type' => 'attendance',
                'start' => $start,
                'end' => $end,
                'attendance_id' => $row['attendance_id'],
                'status' => $row['status'],
                'is_approved' => $row['is_approved'],
                'notes' => $row['notes']
            ];
        }
        
        // 3. マージ処理
        $merged_data = [];
        $all_uids = array_unique(array_merge(array_keys($schedules), array_keys($attendances)));
        
        foreach ($all_uids as $uid) {
            $merged_data[$uid] = [];
            
            $dates = array_unique(array_merge(
                isset($schedules[$uid]) ? array_keys($schedules[$uid]) : [],
                isset($attendances[$uid]) ? array_keys($attendances[$uid]) : []
            ));
            
            foreach ($dates as $date) {
                if (isset($attendances[$uid][$date])) {
                    $merged_data[$uid][$date] = $attendances[$uid][$date];
                } elseif (isset($schedules[$uid][$date])) {
                    $merged_data[$uid][$date] = $schedules[$uid][$date];
                }
            }
        }
        
        return $merged_data;
    }
}

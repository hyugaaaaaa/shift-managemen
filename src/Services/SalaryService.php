<?php

namespace App\Services;

class SalaryService
{
    /**
     * 給与を計算する
     * @param int $normal_minutes 通常勤務時間（分）
     * @param int $night_minutes 深夜勤務時間（分）
     * @param float $hourly_rate 時給
     * @return array [pay_normal, pay_night, total_pay]
     */
    public function calculate(int $normal_minutes, int $night_minutes, float $hourly_rate): array
    {
        $pay_normal = floor($normal_minutes / 60 * $hourly_rate);
        $pay_night = floor($night_minutes / 60 * $hourly_rate * 1.25); // 深夜1.25倍
        
        return [
            'pay_normal' => $pay_normal,
            'pay_night' => $pay_night,
            'subtotal' => $pay_normal + $pay_night
        ];
    }
}

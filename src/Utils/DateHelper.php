<?php

namespace App\Utils;

class DateHelper
{
    /**
     * 日付から日本語の曜日を取得する
     * @param string $date Y-m-d
     * @return string (月), (火) etc.
     */
    public static function getDayOfWeekJa(string $date): string
    {
        $week = ['日', '月', '火', '水', '木', '金', '土'];
        $w = (int)date('w', strtotime($date));
        return '(' . $week[$w] . ')';
    }
}
